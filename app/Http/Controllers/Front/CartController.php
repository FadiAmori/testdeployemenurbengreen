<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Shop\Cart;
use App\Models\Shop\CartItem;
use App\Models\Shop\Order;
use App\Models\Shop\OrderItem;
use App\Models\Shop\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected function userCart(): Cart
    {
        $user = Auth::user();
        return Cart::firstOrCreate(['user_id' => $user->id], ['total_price' => 0]);
    }

    public function index()
    {
        $cart = $this->userCart()->load(['items.product.primaryImage']);
        $subtotal = $cart->items->sum(function ($item) {
            $unit = $item->product->sale_price && $item->product->sale_price < $item->product->price
                ? $item->product->sale_price
                : $item->product->price;
            return $unit * $item->quantity;
        });
        $cart->update(['total_price' => $subtotal]);

        return view('urbangreen.cart', [
            'cart' => $cart,
            'subtotal' => $subtotal,
        ]);
    }

    public function add(Product $product, Request $request): RedirectResponse
    {
        $cart = $this->userCart();
        $qty = max(1, (int) $request->input('quantity', 1));

        $item = $cart->items()->firstOrCreate(['product_id' => $product->id], ['quantity' => 0]);
        $item->increment('quantity', $qty);

        return back()->with('status', 'Product added to cart.');
    }

    public function updateItem(CartItem $item, Request $request): RedirectResponse
    {
        $this->authorizeItem($item);
        $qty = max(1, (int) $request->input('quantity', 1));
        $item->update(['quantity' => $qty]);
        return back();
    }

    public function removeItem(CartItem $item): RedirectResponse
    {
        $this->authorizeItem($item);
        $item->delete();
        return back()->with('status', 'Item removed.');
    }

    protected function authorizeItem(CartItem $item): void
    {
        $userId = Auth::id();
        abort_unless($item->cart && $item->cart->user_id === $userId, 403);
    }

    public function checkout()
    {
        $cart = $this->userCart()->load(['items.product.primaryImage']);
        if ($cart->items->isEmpty()) {
            return redirect()->route('front.cart')->with('status', 'Your cart is empty.');
        }
        $subtotal = $cart->items->sum(function ($item) {
            $unit = $item->product->sale_price && $item->product->sale_price < $item->product->price
                ? $item->product->sale_price
                : $item->product->price;
            return $unit * $item->quantity;
        });
        $shipping = 0;
        $total = $subtotal + $shipping;
        return view('urbangreen.checkout', compact('cart', 'subtotal', 'shipping', 'total'));
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $cart = $this->userCart()->load(['items.product']);
        if ($cart->items->isEmpty()) {
            return redirect()->route('front.cart')->with('status', 'Your cart is empty.');
        }

        $data = $request->validate([
            'shipping_address' => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
        ]);

        $subtotal = $cart->items->sum(function ($item) {
            $unit = $item->product->sale_price && $item->product->sale_price < $item->product->price
                ? $item->product->sale_price
                : $item->product->price;
            return $unit * $item->quantity;
        });

        $order = Order::create([
            'cart_id' => $cart->id,
            'user_id' => Auth::id(),
            'order_date' => now()->toDateString(),
            'total_price' => $subtotal,
            'status' => 'pending',
            'shipping_address' => $data['shipping_address'],
        ]);

        foreach ($cart->items as $item) {
            $unit = $item->product->sale_price && $item->product->sale_price < $item->product->price
                ? $item->product->sale_price
                : $item->product->price;
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price_at_purchase' => $unit,
            ]);
        }

        // Save shipping details back to user for next time
        $user = Auth::user();
        if ($user) {
            $updates = [];
            if (!empty($data['shipping_address'])) { $updates['location'] = $data['shipping_address']; }
            if (!empty($data['first_name'])) { $updates['prenom'] = $data['first_name']; }
            if (!empty($data['last_name'])) { $updates['name'] = $data['last_name']; }
            if (!empty($data['phone_number'])) { $updates['phone'] = $data['phone_number']; }
            if (!empty($updates)) { $user->fill($updates)->save(); }
        }

        // Reset cart
        $cart->items()->delete();
        $cart->update(['total_price' => 0]);

        return redirect()->route('front.orders.index')->with('status', 'Order placed successfully.');
    }
}
