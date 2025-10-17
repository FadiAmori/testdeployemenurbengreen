<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Shop\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function toggle($productId)
    {
        $product = Product::findOrFail($productId);
        $user = Auth::user();

        if ($user->favoriteProducts()->where('product_id', $productId)->exists()) {
            // Remove from favorites
            $user->favoriteProducts()->detach($productId);
            $message = 'Product removed from favorites!';
            $isFavorited = false;
        } else {
            // Add to favorites
            $user->favoriteProducts()->attach($productId);
            $message = 'Product added to favorites!';
            $isFavorited = true;
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_favorited' => $isFavorited
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function index()
    {
        $favoriteProducts = Auth::user()->favoriteProducts()->with(['category', 'maintenance'])->get();
        
        return view('urbangreen.favorites.index', compact('favoriteProducts'));
    }

    public function notifications()
    {
        $user = Auth::user();
        $favoriteProducts = $user->favoriteProducts()->with(['category', 'maintenance', 'notifications'])->get();
        
        // Get all notifications for favorite products
        $notifications = collect();
        foreach ($favoriteProducts as $product) {
            foreach ($product->notifications as $notification) {
                $notifications->push([
                    'id' => $notification->id,
                    'product' => $product,
                    'notification' => $notification,
                    'created_at' => $notification->pivot->created_at ?? $notification->created_at,
                ]);
            }
        }
        
        // Sort by creation date (newest first)
        $notifications = $notifications->sortByDesc('created_at');
        
        return view('urbangreen.notifications.index', compact('notifications', 'favoriteProducts'));
    }
}
