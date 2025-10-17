<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Shop\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $orders = Order::with(['items.product.primaryImage'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('urbangreen.orders.index', compact('orders'));
    }

    public function confirmDelivery(Order $order)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        if ($order->status === 'confirmed') {
            $order->update(['status' => 'delivered']);
        }
        return back()->with('status', 'Thanks! Delivery confirmed.');
    }
}
