<?php

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use App\Models\Shop\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class MaintenanceController extends Controller
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

        return view('dashboard.Maintenance.categoryMaintenance', compact('categories'));
    }
    public function generateSteps($productName)
    {
        // Require the Python ML service to be running. Configure URL via .env if needed.
        $mlBase = env('ML_SERVICE_URL', 'http://127.0.0.1:5001');
        $endpoint = rtrim($mlBase, '/') . '/api/generate-steps';

        $plant = trim(urldecode($productName));
        if ($plant === '') {
            return response()->json(['error' => 'Invalid product name.'], 400);
        }

        try {
            $response = Http::timeout(6)->get($endpoint, ['plant' => $plant]);
        } catch (\Exception $e) {
            Log::error('ML service request failed', ['endpoint' => $endpoint, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'ML service unavailable. Please start the Python service at ' . $mlBase], 503);
        }

        if (!$response->successful()) {
            $body = $response->json() ?? [];
            $msg = $body['error'] ?? $body['message'] ?? 'ML service error';
            return response()->json(['error' => $msg], $response->status());
        }

        $data = $response->json();
        if (empty($data)) {
            return response()->json(['message' => 'No predefined steps found for this plant.'], 404);
        }

        // Return the ML service results directly
        return response()->json($data);
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
            $query->withTrashed()
                  ->withCount('favoritedByUsers')
                  ->orderBy('name');
            // Optional material name filter
            if ($material = $request->query('material')) {
                $query->where('attributes->material', 'like', "%{$material}%");
            }
        }])->findOrFail($categoryId);

        return view('dashboard.Maintenance.productMaintenanceCategory', compact('category'));
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
        // Validate everything except strict file rules on video (to avoid generic "failed to upload" from PHP)
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
            // Now that PHP accepted the file, enforce type/size with Validator
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
        return view('dashboard.Maintenance.show', compact('maintenance'));
    }

    /**
     * Download maintenance as PDF for a product.
     *
     * @param int $productId
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf($productId)
    {
        $maintenance = Maintenance::with(['product', 'material', 'optional'])->where('product_id', $productId)->firstOrFail();

        // If the Dompdf package is available use it, else return HTML view as fallback
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('dashboard.Maintenance.pdf.maintenance_pdf', compact('maintenance'));
            $fileName = 'maintenance_' . $maintenance->product->id . '_' . now()->format('Ymd_His') . '.pdf';
            return $pdf->download($fileName);
        }

        // Fallback for environments without the package - return the HTML view
        return view('dashboard.Maintenance.pdf.maintenance_pdf', compact('maintenance'));
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

    /**
     * Generate AI-powered notification suggestions for a product
     * 
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateAINotifications(Product $product)
    {
        try {
            // Configuration
            $mlBase = env('ML_NOTIFICATION_SERVICE_URL', 'http://127.0.0.1:5002');
            $endpoint = rtrim($mlBase, '/') . '/api/generate-notifications';
            
            // Call AI service
            $response = Http::timeout(10)->get($endpoint, [
                'plant' => $product->name
            ]);
            
            if (!$response->successful()) {
                Log::error('AI Notification Service Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'AI notification service unavailable'
                ], 503);
            }
            
            $aiData = $response->json();
            $notifications = $aiData['notifications'] ?? [];
            
            if (empty($notifications)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No AI suggestions found for this product.',
                    'count' => 0,
                    'created' => 0
                ]);
            }
            
            $created = 0;
            $attached = 0;
            
            foreach ($notifications as $notif) {
                // Check if notification already exists (using 'name' and 'description' columns)
                $notification = \App\Models\Shop\Notification::firstOrCreate(
                    [
                        'name' => $notif['title'],
                        'description' => $notif['message']
                    ]
                );
                
                if ($notification->wasRecentlyCreated) {
                    $created++;
                }
                
                // Attach to product if not already attached
                if (!$product->notifications()->where('notification_id', $notification->id)->exists()) {
                    // Convert 'everyday' to JSON array format or keep as is
                    $days = $notif['days'] ?? 'everyday';
                    if ($days === 'everyday') {
                        $days = json_encode(['everyday']);
                    } elseif (is_numeric($days)) {
                        // Keep numeric days as is for compatibility
                        $days = (int)$days;
                    } elseif (is_string($days) && !is_numeric($days)) {
                        // Convert string days to JSON array
                        $days = json_encode([$days]);
                    }
                    
                    $product->notifications()->attach($notification->id, [
                        'days' => $days,
                        'time' => $notif['time'] ?? '09:00:00',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    $attached++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "AI suggestions applied successfully!",
                'count' => count($notifications),
                'created' => $created,
                'attached' => $attached,
                'notifications' => $notifications
            ]);
            
        } catch (\Exception $e) {
            Log::error('AI Notifications Error', [
                'product_id' => $product->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
