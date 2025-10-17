<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Event\Event;
use App\Models\Event\EventCategory;
use Illuminate\View\View;
use App\Models\Statute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\RedirectResponse;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UrbanGreenController extends Controller 
{
    public function home(): View
    {
        return view('urbangreen.home');
    }

    public function event(Request $request): View
    {
        $categories = EventCategory::where('is_active', true)->get();
        $query = Event::published()->upcoming()->with('category', 'user'); // Use scopes for published/upcoming
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhereHas('category', function ($cat) use ($search) {
                      $cat->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function ($user) use ($search) {
                      $user->where('name', 'like', "%{$search}%");
                  });
            })->orderByRaw("
                CASE 
                    WHEN title LIKE ? THEN 1
                    WHEN description LIKE ? THEN 2
                    WHEN location LIKE ? THEN 3
                    WHEN EXISTS (SELECT 1 FROM event_categories WHERE event_categories.id = events.category_id AND event_categories.name LIKE ?) THEN 4
                    WHEN EXISTS (SELECT 1 FROM users WHERE users.id = events.user_id AND users.name LIKE ?) THEN 5
                    ELSE 6
                END ASC
            ", ["%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"]);
        }
        if (!$request->filled('search')) {
            $query->orderBy('event_date', 'desc');
        }
        if ($request->filled('sort')) {
            $sort = $request->sort;
            switch ($sort) {
                case 'date_asc':
                    $query->orderBy('event_date', 'asc');
                    break;
                case 'attendees_desc':
                    $query->withCount('users')->orderBy('users_count', 'desc');
                    break;
                case 'title_asc':
                    $query->orderBy('title', 'asc');
                    break;
                case 'relevance_desc':
                default:
                    // Already handled by search or default to date_desc
                    break;
            }
        }
        $events = $query->paginate(6)->withQueryString();
        return view('urbangreen.event', compact('events', 'categories'));
    }

    // New: Event details page
    public function showEvent(Event $event): View
    {
        if (!$event->is_published) {
            abort(404);
        }

        $event->load(['users' => function ($query) {
            // Qualify columns to avoid ambiguous `id` when pivot table contains an `id` column
            $query->select('users.id', 'users.name', 'users.profile_photo');
        }, 'category', 'user']);

        // Fetch related events (same category, excluding current event)
        $relatedEvents = Event::published()
            ->upcoming()
            ->where('category_id', $event->category_id)
            ->where('id', '!=', $event->id)
            ->with('category')
            ->latest()
            ->take(3)
            ->get();

        return view('urbangreen.event-show', compact('event', 'relatedEvents'));
    }

    // Show dataset event (from ML recommendations) - for demo purposes
    public function showDatasetEvent($eventId): View
    {
        // Read event from CSV dataset
        $csvPath = base_path('urban_ml_service/datasets/urban_events_details.csv');
        
        if (!file_exists($csvPath)) {
            abort(404, 'Dataset not found');
        }

        $event = null;
        $handle = fopen($csvPath, 'r');
        $headers = fgetcsv($handle);
        
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            if ($data['event_id'] === "event_$eventId") {
                $event = $data; // Keep as array for blade template
                break;
            }
        }
        fclose($handle);

        if (!$event) {
            abort(404, 'Event not found in dataset');
        }

        return view('urbangreen.dataset-event-show', compact('event'));
    }

    // Enroll current user (or guest info) to an event
    public function enrollEvent(Request $request, Event $event): RedirectResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
        ]);

        if (Schema::hasTable('event_user')) {
            try {
                // avoid duplicate entries
                $userId = optional(Auth::user())->id;
                if ($userId) {
                    $exists = DB::table('event_user')
                        ->where('event_id', $event->id)
                        ->where('user_id', $userId)
                        ->exists();
                    if (! $exists) {
                        DB::table('event_user')->insert([
                            'event_id' => $event->id,
                            'user_id' => $userId,
                            'attendance_status' => 'pending', // Set default status
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                return back()->with('status', 'Joined the event (pivot). Note: ' . $e->getMessage());
            }
        } elseif (Schema::hasTable('event_enrollments')) {
            try {
                $data = [
                    'event_id' => $event->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if (Schema::hasColumn('event_enrollments', 'user_id')) {
                    $data['user_id'] = optional(Auth::user())->id;
                }
                if (Schema::hasColumn('event_enrollments', 'attendance_status')) {
                    $data['attendance_status'] = 'pending';
                }
                if (Schema::hasColumn('event_enrollments', 'name') && $request->filled('name')) {
                    $data['name'] = $request->string('name');
                }
                if (Schema::hasColumn('event_enrollments', 'email') && $request->filled('email')) {
                    $data['email'] = $request->string('email');
                }
                if (Schema::hasColumn('event_enrollments', 'phone') && $request->filled('phone')) {
                    $data['phone'] = $request->string('phone');
                }

                DB::table('event_enrollments')->insert($data);
            } catch (\Throwable $e) {
                return back()->with('status', 'Enrollment recorded. (Note: details not saved: ' . $e->getMessage() . ')');
            }
        }

        return back()->with('status', 'Enrollment successful! We\'ll contact you with details.');
    }

    // Confirm attendance (user self-confirm)
    public function confirmAttendance(Request $request, Event $event): RedirectResponse
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }
        if (!$event->users->contains($user->id)) {
            abort(403);
        }
        if ($event->event_date->isPast()) {
            return back()->with('error', 'Event has passed.');
        }

        // Use model relationship if possible, fallback to DB
        if (method_exists($event, 'users')) {
            $event->users()->updateExistingPivot($user->id, ['attendance_status' => 'confirmed']);
        } else {
            DB::table('event_user')
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->update(['attendance_status' => 'confirmed']);
        }

        return back()->with('status', 'Attendance confirmed!');
    }

    public function shop(): View
    {
        return view('urbangreen.shop');
    }

    public function shopDetails(): View
    {
        return view('urbangreen.shop-details');
    }

    public function cart(): View
    {
        return view('urbangreen.cart');
    }

    public function checkout(): View
    {
        return view('urbangreen.checkout');
    }

    public function portfolio(): View
    {
        return view('urbangreen.portfolio');
    }

    public function singlePortfolio(): View
    {
        return view('urbangreen.single-portfolio');
    }

    public function blog(): View
    {
        $statutes = Statute::latest()->paginate(6);
        $recentStatutes = Statute::latest()->take(4)->get();
        return view('urbangreen.blog', compact('statutes', 'recentStatutes'));
    }

    public function singlePost(): View
    {
        return view('urbangreen.single-post');
    }

    public function maintenance(): View
    {
        return view('urbangreen.maintenance');
    }
}