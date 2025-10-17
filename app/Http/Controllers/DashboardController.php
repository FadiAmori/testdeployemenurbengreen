<?php

namespace App\Http\Controllers;

use App\Models\Event\Event;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user && $user->isAdmin()) {
            $stats = [
                'total_users' => User::count(),
                'blocked_users' => User::where('is_blocked', true)->count(),
                'total_products' => Product::count(),
                'total_orders' => Order::count(),
                'upcoming_events' => Event::where('event_date', '>=', now())->count(),
            ];

            $recentUsers = User::latest()->take(5)->get();
            $recentOrders = Order::latest()->take(5)->get();

            return view('dashboard.pages.admin.dashboard', compact('stats', 'recentUsers', 'recentOrders'));
        }

        return view('dashboard.pages.user.dashboard', [
            'upcomingEvents' => Event::where('event_date', '>=', now())->orderBy('event_date')->take(3)->get(),
            'favoritesCount' => $user?->favoriteProducts()->count() ?? 0,
            'ordersCount' => $user ? Order::where('user_id', $user->id)->count() : 0,
        ]);
    }
}
