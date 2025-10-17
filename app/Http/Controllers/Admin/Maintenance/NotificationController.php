<?php

namespace App\Http\Controllers\Admin\Maintenance;

use App\Http\Controllers\Controller;
use App\Models\Shop\Notification; // Adjust namespace if different
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::all();
        $activePage = 'notifications';
        $titlePage = 'Notifications';
        return view('dashboard.Maintenance.notifications', compact('notifications', 'activePage', 'titlePage'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Notification::create($validated);

        return Redirect::back()->with('status', 'Notification créée avec succès!');
    }

    public function update(Request $request, Notification $notification)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $notification->update($validated);

        return Redirect::back()->with('status', 'Notification mise à jour avec succès!');
    }

    public function destroy(Notification $notification)
    {
        $notification->delete();

        return Redirect::back()->with('status', 'Notification supprimée avec succès!');
    }

    public function showProductNotifications($productId)
    {
        $product = \App\Models\Shop\Product::findOrFail($productId);
        $notifications = $product->notifications()->get();
        $allNotifications = Notification::all();
        $activePage = 'maintenance';
        $titlePage = 'Product Notifications - ' . $product->name;
        
    // pass productNotifications for view compatibility
    $productNotifications = $notifications;

    // pass both variable names so views expecting either will work
    $notifications = $notifications;

    return view('dashboard.Maintenance.produitnotification', compact('product', 'productNotifications', 'allNotifications', 'notifications', 'activePage', 'titlePage'));
    }

    public function attachToProduct(Request $request, $productId)
    {
        $product = \App\Models\Shop\Product::findOrFail($productId);
        $data = $request->validate([
            'notification_id' => 'required|integer|exists:notifications,id',
            'days' => 'nullable|array',
            'days.*' => 'string',
            'time' => 'nullable|date_format:H:i',
        ]);

        $notificationId = $data['notification_id'];

        if ($product->notifications()->where('notification_id', $notificationId)->exists()) {
            return Redirect::back()->with('status', 'Cette notification est déjà associée à ce produit.');
        }

        // normalize days
        $days = $data['days'] ?? [];
        // if 'everyday' selected, store as ['everyday']
        if (in_array('everyday', $days)) {
            $days = ['everyday'];
        }

        // Build attach payload conditionally depending on whether the pivot columns exist
        $attachData = [];
        if (Schema::hasTable('notification_product') && Schema::hasColumn('notification_product', 'days')) {
            $attachData['days'] = json_encode($days);
        }
        if (Schema::hasTable('notification_product') && Schema::hasColumn('notification_product', 'time')) {
            $attachData['time'] = $data['time'] ?? null;
        }

        // If there are no extra pivot columns, attach without additional data
        if (empty($attachData)) {
            $product->notifications()->attach($notificationId);
        } else {
            $product->notifications()->attach($notificationId, $attachData);
        }

        return Redirect::back()->with('status', 'Notification associée au produit avec succès!');
    }

    public function detachFromProduct($productId, $notificationId)
    {
        $product = \App\Models\Shop\Product::findOrFail($productId);
        $product->notifications()->detach($notificationId);
        
        return Redirect::back()->with('status', 'Notification retirée du produit avec succès!');
    }

    public function updateProductNotification(Request $request, $productId, $notificationId)
    {
        $product = \App\Models\Shop\Product::findOrFail($productId);

        $data = $request->validate([
            'days' => 'nullable|array',
            'days.*' => 'string',
            'time' => 'nullable|date_format:H:i',
        ]);

        $days = $data['days'] ?? [];
        if (in_array('everyday', $days)) {
            $days = ['everyday'];
        }

        // Only update pivot columns if they exist
        $updateData = [];
        if (Schema::hasTable('notification_product') && Schema::hasColumn('notification_product', 'days')) {
            $updateData['days'] = json_encode($days);
        }
        if (Schema::hasTable('notification_product') && Schema::hasColumn('notification_product', 'time')) {
            $updateData['time'] = $data['time'] ?? null;
        }

        if (! empty($updateData)) {
            $product->notifications()->updateExistingPivot($notificationId, $updateData);
        }

        return Redirect::back()->with('status', 'Planning de notification mis à jour!');
    }
}