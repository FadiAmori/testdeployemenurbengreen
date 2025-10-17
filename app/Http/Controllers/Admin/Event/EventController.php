<?php
// app/Http/Controllers/Admin/Event/EventController.php

namespace App\Http\Controllers\Admin\Event;

use App\Http\Controllers\Controller;
use App\Models\Event\Event;
use App\Models\Event\EventCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;
use App\Exports\EventsExport;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with(['user', 'category'])->withCount('users')->latest();

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

        if ($request->filled('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $events = $query->paginate(10);
        $categories = EventCategory::where('is_active', true)->get();
        return view('dashboard.pages.events.index', compact('events', 'categories'));
    }

    public function create()
    {
        $categories = EventCategory::where('is_active', true)->get();
        return view('dashboard.pages.events.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date|after:now',
            'location' => 'required|max:255',
            'category_id' => 'nullable|exists:event_categories,id',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $validated['is_published'] = $request->has('is_published');
        $validated['user_id'] = Auth::id();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        Event::create($validated);

        return redirect()->route('admin.event.index')->with('status', 'Event created successfully.');
    }

    public function show(Event $event)
    {
        $event->load(['users' => function ($query) {
            $query->select('users.id', 'users.name', 'users.profile_photo');
        }]);
        return view('dashboard.pages.events.show', compact('event'));
    }

    public function edit(Event $event)
    {
        $categories = EventCategory::where('is_active', true)->get();
        $event->load(['users' => function ($query) {
            $query->select('users.id', 'users.name', 'users.profile_photo');
        }]);
        return view('dashboard.pages.events.edit', compact('event', 'categories'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'description' => 'nullable|string',
            'event_date' => 'required|date|after:now',
            'location' => 'required|max:255',
            'category_id' => 'nullable|exists:event_categories,id',
            'image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        $validated['is_published'] = $request->has('is_published');

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update($validated);

        return redirect()->route('admin.event.index')->with('status', 'Event updated successfully.');
    }

    public function updateAttendance(Request $request, Event $event, $userId)
    {
        if (Auth::id() !== $event->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'attendance_status' => 'required|in:pending,confirmed,attended,absent',
        ]);

        $event->users()->updateExistingPivot($userId, ['attendance_status' => $validated['attendance_status']]);

        return back()->with('status', 'Attendance updated.');
    }

    public function bulkUpdateAttendance(Request $request, Event $event)
    {
        if (Auth::id() !== $event->user_id && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'attendances' => 'required|array',
            'attendances.*.user_id' => 'exists:users,id',
            'attendances.*.status' => 'in:pending,confirmed,attended,absent',
        ]);

        foreach ($validated['attendances'] as $data) {
            $event->users()->updateExistingPivot($data['user_id'], ['attendance_status' => $data['status']]);
        }

        return back()->with('status', 'Bulk update complete.');
    }

    public function calendarData(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $events = Event::with('category')
            ->withCount('users')
            ->where('is_published', true)
            ->whereBetween('event_date', [$start, $end])
            ->get()
            ->map(function ($event) {
                $color = $event->category ? '#28a745' : '#6c757d';
                return [
                    'id' => $event->id,
                    'title' => $event->title . ' (' . $event->users_count . ' attendees)',
                    'start' => $event->event_date->toISOString(),
                    'url' => route('admin.event.show', $event),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'extendedProps' => [
                        'location' => $event->location,
                        'description' => Str::limit($event->description, 100),
                        'category' => $event->category?->name ?? 'Uncategorized'
                    ]
                ];
            });

        return response()->json($events);
    }

    public function frontCalendarData(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $events = Event::with('category')
            ->withCount('users')
            ->where('is_published', true)
            ->whereBetween('event_date', [$start, $end])
            ->get()
            ->map(function ($event) {
                $color = $event->category ? '#28a745' : '#6c757d';
                return [
                    'id' => $event->id,
                    'title' => $event->title . ' (' . $event->users_count . ' attendees)',
                    'start' => $event->event_date->toISOString(),
                    'url' => route('front.event.show', $event),
                    'backgroundColor' => $color,
                    'borderColor' => $color,
                    'extendedProps' => [
                        'location' => $event->location,
                        'description' => Str::limit($event->description, 100),
                        'category' => $event->category?->name ?? 'Uncategorized'
                    ]
                ];
            });

        return response()->json($events);
    }

    public function exportExcel(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $query = Event::with(['user', 'category'])->withCount('users');

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
            });
        }

        if ($request->filled('published')) {
            $query->where('is_published', $request->boolean('published'));
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $events = $query->get();

        return \Excel::create('events_' . now()->format('Y-m-d_H-i-s'), function($excel) use ($events) {
            $excel->sheet('Events', function($sheet) use ($events) {
                $sheet->row(1, [
                    'Title', 'Description', 'Date', 'Location', 'Category', 
                    'Attendees Count', 'Max Participants', 'Status', 'Created By', 'Created At'
                ]);

                $row = 2;
                foreach ($events as $event) {
                    $sheet->row($row++, [
                        $event->title,
                        $event->description ?? 'N/A',
                        $event->event_date->format('Y-m-d H:i:s'),
                        $event->location,
                        $event->category?->name ?? 'No Category',
                        $event->users_count,
                        $event->max_participants ?? 'Unlimited',
                        $event->is_published ? 'Published' : 'Draft',
                        $event->user?->name ?? 'N/A',
                        $event->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });
        })->export('xlsx');
    }

public function exportPdf(Request $request)
{
    // Ensure admin access (using middleware or manual check)
    if (!Auth::check() || !Auth::user()->isAdmin()) {
        abort(403, 'Unauthorized action.');
    }

    // Increase execution time and memory limit for PDF generation
    ini_set('max_execution_time', 120); // 120 seconds
    ini_set('memory_limit', '256M');   // 256 MB

    $query = Event::with(['user', 'category'])->withCount('users');

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
        });
    }

    if ($request->filled('published')) {
        $query->where('is_published', $request->boolean('published'));
    }

    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    // Limit events to prevent timeout (adjust as needed)
    $events = $query->take(100)->get();

    // Log query performance
    \Log::info('PDF Export: Events fetched', [
        'count' => $events->count(),
        'user_id' => Auth::id(),
    ]);

    try {
        $pdf = Pdf::setOptions([
            'dpi' => 150,
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false, // Disable for performance unless images are needed
            'isFontSubsettingEnabled' => true,
        ])->loadView('dashboard.pages.events.pdf', compact('events'))
          ->setPaper('a4', 'portrait');

        $filename = 'events_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        return $pdf->stream($filename); // Stream instead of download for large PDFs
    } catch (\Exception $e) {
        \Log::error('PDF Export Error: ' . $e->getMessage(), [
            'events_count' => $events->count(),
            'user_id' => Auth::id(),
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
    }
}

public function export(Request $request)
{
    if (!Auth::check() || !Auth::user()->isAdmin()) {
        abort(403, 'Unauthorized action.');
    }

    $query = Event::with(['user', 'category'])->withCount('users');

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
        });
    }

    if ($request->filled('published')) {
        $query->where('is_published', $request->boolean('published'));
    }

    if ($request->filled('category')) {
        $query->where('category_id', $request->category);
    }

    $events = $query->get();

    return Excel::create('events_' . now()->format('Y-m-d_H-i-s'), function($excel) use ($events) {
        $excel->sheet('Events', function($sheet) use ($events) {
            $sheet->fromArray($events->map(function ($event) {
                return [
                    'ID' => $event->id,
                    'Title' => $event->title,
                    'Description' => $event->description,
                    'Event Date' => $event->event_date->format('Y-m-d H:i:s'),
                    'Location' => $event->location,
                    'Category' => $event->category ? $event->category->name : 'Uncategorized',
                    'Organizer' => $event->user ? $event->user->name : 'Unknown',
                    'Attendees' => $event->users_count,
                    'Published' => $event->is_published ? 'Yes' : 'No',
                ];
            })->toArray());
        });
    })->download('xlsx');
}

}