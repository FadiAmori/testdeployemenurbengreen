<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaintenanceClientController extends Controller
{
    /**
     * Display a listing of categories with optional material name filtering.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Category::query()->with(['products' => function ($query) {
            $query->withTrashed()->orderBy('name');
        }])->orderBy('name');

        // Optional material name filter
        if ($material = $request->query('material')) {
            $query->whereHas('products', function ($q) use ($material) {
                $q->withTrashed()->where('attributes->material', 'like', "%{$material}%");
            });
        }

        $categories = $query->get();

        return view('urbangreen.Maintenance.categoryMaintenance', compact('categories'));
    }

    /**
     * Display the products of a specific category with optional material filtering.
     *
     * @param Request $request
     * @param int $categoryId
     * @return \Illuminate\View\View
     */
    public function show(Request $request, $categoryId)
    {
        $category = Category::with(['products' => function ($query) use ($request) {
            $query->withTrashed()->orderBy('name');
            // Optional material name filter
            if ($material = $request->query('material')) {
                $query->where('attributes->material', 'like', "%{$material}%");
            }
        }])->findOrFail($categoryId);

        return view('urbangreen.Maintenance.ProductMaintenance', compact('category'));
    }

    /**
     * Show the form for creating a new maintenance record.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $products = Product::orderBy('name')->get();
return view('dashboard.Maintenance.create', compact('products'));    }

    /**
     * Store a newly created maintenance record.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id|unique:maintenances,product_id',
            'description' => 'nullable|string',
            'steps' => 'nullable|array',
            'steps.*.title' => 'nullable|string|max:255',
            'steps.*.description' => 'nullable|string',
            'photo' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video' => 'nullable',
            'material_id' => 'nullable|exists:products,id',
            'optional_id' => 'nullable|exists:products,id',
        ]);

        $maintenanceData = $request->only(['product_id', 'description', 'steps', 'material_id', 'optional_id']);

        // Handle file uploads
        if ($request->hasFile('photo')) {
            try {
                Storage::disk('public')->makeDirectory('maintenances/photos');
                $maintenanceData['photo'] = $request->file('photo')->store('maintenances/photos', 'public');
            } catch (\Throwable $e) {
                return back()->withErrors(['photo' => 'The photo failed to upload. ' . $e->getMessage()])->withInput();
            }
        }

        if ($request->file('video')) {
            $video = $request->file('video');
            if ($video->getError() !== UPLOAD_ERR_OK) {
                $up = ini_get('upload_max_filesize');
                $pm = ini_get('post_max_size');
                return back()->withErrors(['video' => "Video upload failed (PHP limits?). upload_max_filesize=$up, post_max_size=$pm"]) ->withInput();
            }
            \Validator::validate(['video' => $video], [
                'video' => 'mimes:mp4,webm,quicktime,mov,avi,flv,mkv,wmv|max:204800',
            ]);
            try {
                Storage::disk('public')->makeDirectory('maintenances/videos');
                $maintenanceData['video'] = $video->store('maintenances/videos', 'public');
            } catch (\Throwable $e) {
                return back()->withErrors(['video' => 'The video failed to upload. ' . $e->getMessage()])->withInput();
            }
        }

        Maintenance::create($maintenanceData);

        return redirect()->route('maintenance.show', $maintenanceData['product_id'])
            ->with('success', 'Maintenance record created successfully.');
    }

    /**
     * Display the specified maintenance record.
     *
     * @param int $productId
     * @return \Illuminate\View\View
     */
    public function showMaintenance($productId)
    {
        $maintenance = Maintenance::with(['product', 'material', 'optional'])->where('product_id', $productId)->firstOrFail();
        return view('urbangreen.maintenance.show', compact('maintenance'));
    }

    /**
     * Show the form for editing the specified maintenance record.
     *
     * @param int $productId
     * @return \Illuminate\View\View
     */
    public function edit($productId)
    {
        $maintenance = Maintenance::where('product_id', $productId)->firstOrFail();
        $products = Product::orderBy('name')->get();
        return view('dashboard.Maintenance.edit', compact('maintenance', 'products'));
    }

    /**
     * Update the specified maintenance record.
     *
     * @param Request $request
     * @param int $productId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $productId)
    {
        $maintenance = Maintenance::where('product_id', $productId)->firstOrFail();

        $validated = $request->validate([
            'description' => 'nullable|string',
            'steps' => 'nullable|array',
            'steps.*.title' => 'nullable|string|max:255',
            'steps.*.description' => 'nullable|string',
            'photo' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video' => 'nullable',
            'material_id' => 'nullable|exists:products,id',
            'optional_id' => 'nullable|exists:products,id',
        ]);

        $maintenanceData = $request->only(['description', 'steps', 'material_id', 'optional_id']);

        // Handle file uploads
        if ($request->hasFile('photo')) {
            try {
                if ($maintenance->photo) {
                    Storage::disk('public')->delete($maintenance->photo);
                }
                Storage::disk('public')->makeDirectory('maintenances/photos');
                $maintenanceData['photo'] = $request->file('photo')->store('maintenances/photos', 'public');
            } catch (\Throwable $e) {
                return back()->withErrors(['photo' => 'The photo failed to upload. ' . $e->getMessage()])->withInput();
            }
        }

        if ($request->file('video')) {
            $video = $request->file('video');
            if ($video->getError() !== UPLOAD_ERR_OK) {
                $up = ini_get('upload_max_filesize');
                $pm = ini_get('post_max_size');
                return back()->withErrors(['video' => "Video upload failed (PHP limits?). upload_max_filesize=$up, post_max_size=$pm"]) ->withInput();
            }
            \Validator::validate(['video' => $video], [
                'video' => 'mimes:mp4,webm,quicktime,mov,avi,flv,mkv,wmv|max:204800',
            ]);
            try {
                if ($maintenance->video) {
                    Storage::disk('public')->delete($maintenance->video);
                }
                Storage::disk('public')->makeDirectory('maintenances/videos');
                $maintenanceData['video'] = $video->store('maintenances/videos', 'public');
            } catch (\Throwable $e) {
                return back()->withErrors(['video' => 'The video failed to upload. ' . $e->getMessage()])->withInput();
            }
        }

        $maintenance->update($maintenanceData);

        return redirect()->route('maintenance.show', $productId)
            ->with('success', 'Maintenance record updated successfully.');
    }

    public function destroy($productId)
{
    $maintenance = Maintenance::where('product_id', $productId)->firstOrFail();
    $maintenance->delete();

    return redirect()->route('maintenance.category', ['categoryId' => $maintenance->product->category_id])
        ->with('success', 'Maintenance record deleted successfully.');
}
}
