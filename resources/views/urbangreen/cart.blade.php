@extends('urbangreen.layouts.main')
@section('content')
<!-- ##### Breadcrumb Area Start ##### -->
    <div class="breadcrumb-area">
        <!-- Top Breadcrumb Area -->
        <div class="top-breadcrumb-area bg-img bg-overlay d-flex align-items-center justify-content-center" style="background-image: url({{ asset('urbangreen/img/bg-img/24.jpg') }});">
            <h2>Cart</h2>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('front.home', [], false) }}"><i class="fa fa-home"></i> Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Cart</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- ##### Breadcrumb Area End ##### -->

    <!-- ##### Cart Area Start ##### -->
    <div class="cart-area section-padding-0-100 clearfix">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="cart-table clearfix">
                        @if(session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif
                        <table class="table table-responsive">
                            <thead>
                                <tr>
                                    <th>Products</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>TOTAL</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @php($cart = $cart ?? null)
                                @if(($cart?->items ?? collect())->count() === 0)
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Your cart is empty.</td>
                                    </tr>
                                @endif
                                @if(($cart?->items ?? collect())->count())
                                    @each('urbangreen.partials.cart-row', ($cart?->items ?? []), 'item')
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Cart Totals -->
                <div class="col-12 col-lg-6 ms-auto">
                    <div class="cart-totals-area mt-70">
                        <h5 class="title--">Cart Total</h5>
                        @php($subtotal = $subtotal ?? ($cart?->items?->sum(fn($i) => (($i->product->sale_price && $i->product->sale_price < $i->product->price) ? $i->product->sale_price : $i->product->price) * $i->quantity) ?? 0))
                        <div class="subtotal d-flex justify-content-between">
                            <h5>Subtotal</h5>
                            <h5>${{ number_format($subtotal, 2) }}</h5>
                        </div>
                        <div class="total d-flex justify-content-between">
                            <h5>Total</h5>
                            <h5>${{ number_format($subtotal, 2) }}</h5>
                        </div>
                        <div class="checkout-btn">
                            <a href="{{ route('front.checkout') }}" class="btn alazea-btn w-100">PROCEED TO CHECKOUT</a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <!-- ##### Cart Area End ##### -->
@endsection
