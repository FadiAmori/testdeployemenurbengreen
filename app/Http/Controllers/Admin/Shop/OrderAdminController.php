<?php

namespace App\Http\Controllers\Admin\Shop;

use App\Http\Controllers\Controller;
use App\Models\Shop\Order;
use Illuminate\Http\RedirectResponse;

class OrderAdminController extends Controller
{
    public function confirm(Order $order): RedirectResponse
    {
        if ($order->status !== 'confirmed') {
            $order->loadMissing('items.product');
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stock = max(0, (int) $product->stock - (int) $item->quantity);
                    $product->save();
                }
            }
            $order->update(['status' => 'confirmed']);
        }
        return back()->with('status', 'Commande confirmée.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $order->delete();
        return back()->with('status', 'Commande supprimée.');
    }
}

