<?php

namespace App\Http\Controllers;

use App\Models\Statute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class StatuteController extends Controller
{
    // List statutes (with optional search)
    public function index(Request $request)
    {
        $query = Statute::query();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('titre', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }

        $statutes = $query->latest()->paginate(6)->appends(['search' => $request->search]);
        $recentStatutes = Statute::latest()->take(4)->get();

        return view('urbangreen.blog', compact('statutes', 'recentStatutes'));
    }

    public function create()
    {
        return view('statutes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $data = $request->only(['titre', 'description']);
        $data['user_id'] = auth()->id(); // Save the user who created the statute

        if ($request->hasFile('photo')) {
            $dir = public_path('uploads/statutes');
            File::ensureDirectoryExists($dir, 0755, true);
            $fileName = time() . '_' . $request->file('photo')->getClientOriginalName();
            $request->file('photo')->move($dir, $fileName);
            $data['photo'] = 'uploads/statutes/' . $fileName;
        }

        Statute::create($data);

        return redirect()->route('front.blog')->with('success', 'Statute added successfully!');
    }

    public function edit(Statute $statute)
    {
        return view('statutes.edit', compact('statute'));
    }

    public function update(Request $request, Statute $statute)
    {
        $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $statute->titre = $request->titre;
        $statute->description = $request->description;

        if ($request->hasFile('photo')) {
            $dir = public_path('uploads/statutes');
            File::ensureDirectoryExists($dir, 0755, true);
            $fileName = time() . '_' . $request->file('photo')->getClientOriginalName();
            $request->file('photo')->move($dir, $fileName);
            $statute->photo = 'uploads/statutes/' . $fileName;
        }

        $statute->save();

        return redirect()->back()->with('success', 'Statute updated successfully!');
    }

    public function destroy(Statute $statute)
    {
        $statute->delete();
        return redirect()->route('statutes.index')->with('success', 'Statute deleted successfully.');
    }

    public function dashboardIndex(Request $request)
    {
        // Build query with search functionality
        $query = Statute::with(['reactions.user'])
            ->withCount('comentes');
        
        // Apply search filter if present
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('titre', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
            });
        }
        
        // Fetch statutes
        $statutes = $query->latest()->paginate(10);

        // Max comments for scaling individual doughnut charts
        $maxComments = $statutes->max('comentes_count') ?: 1;

        // Data for overview chart
        $ids = $statutes->pluck('id')->values()->toArray();
        $labels = $statutes->map(function ($s) {
            return Str::limit($s->titre, 20) ?: 'Untitled'; // Fallback for empty titles
        })->values()->toArray();
        $commentsData = $statutes->pluck('comentes_count')->values()->toArray();

        // Ensure non-empty data to avoid chart errors
        if (empty($commentsData) || array_sum($commentsData) === 0) {
            $commentsData = [0]; // Fallback for empty data
            $labels = ['No Comments'];
            $ids = [0];
        }

        return view('dashboard.pages.blog', compact('statutes', 'maxComments', 'ids', 'labels', 'commentsData'));
    }

    /**
     * Return basic stats for statutes as JSON (comments, likes, dislikes counts).
     */
    public function statsJson()
    {
        $statutes = Statute::select('id', 'titre')
            ->withCount('comentes')
            ->withCount([
                'reactions as likes_count' => function ($q) { $q->where('reaction', 'like'); },
                'reactions as dislikes_count' => function ($q) { $q->where('reaction', 'dislike'); },
            ])
            ->latest()
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'titre' => $s->titre,
                    'comentes_count' => $s->comentes_count,
                    'likes_count' => $s->likes_count,
                    'dislikes_count' => $s->dislikes_count,
                ];
            });

        return response()->json($statutes);
    }
}
 
