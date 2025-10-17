@extends('urbangreen.layouts.main')
@section('content')
<!-- ##### Breadcrumb Area Start ##### -->
    <div class="breadcrumb-area">
        <!-- Top Breadcrumb Area -->
        <div class="top-breadcrumb-area bg-img bg-overlay d-flex align-items-center justify-content-center" style="background-image: url({{ asset('urbangreen/img/bg-img/24.jpg') }});">
            <h2>Checkout</h2>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('front.home', [], false) }}"><i class="fa fa-home"></i> Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Checkout</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- ##### Breadcrumb Area End ##### -->

    <!-- ##### Checkout Area Start ##### -->
    <div class="checkout_area mb-100">
        <div class="container">
            <div class="row justify-content-between">
                <div class="col-12 col-lg-7">
                    <div class="checkout_details_area clearfix">
                        <h5>Shipping Details</h5>
                        @if(session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif
                        <form action="{{ route('front.checkout.place') }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', auth()->user()->prenom) }}" required>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', auth()->user()->name) }}" required>
                                </div>
                                <div class="col-12 mb-4">
                                    <label for="email_address">Email Address *</label>
                                    <input type="email" class="form-control" id="email_address" name="email_address" value="{{ old('email_address', auth()->user()->email) }}">
                                </div>
                                <div class="col-12 mb-4">
                                    <label for="phone_number">Phone Number *</label>
                                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', auth()->user()->phone) }}">
                                </div>
                                <div class="col-12 mb-4">
                                    <label for="company">Company Name</label>
                                    <input type="text" class="form-control" id="company" value="">
                                </div>
                                <div class="col-12 mb-4">
                                    <label for="address">Address *</label>
                                    <input type="text" class="form-control" id="address" name="shipping_address" value="{{ old('shipping_address', auth()->user()->location) }}" required>
                                    @error('shipping_address')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="city">Town/City *</label>
                                    <input type="text" class="form-control" id="city" value="">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="state">State/Province *</label>
                                    <input type="text" class="form-control" id="state" value="">
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="country">Country</label>
                                    <select class="custom-select d-block w-100" id="country">
                                        <option value="usa">United States</option>
                                        <option value="uk">United Kingdom</option>
                                        <option value="ger">Germany</option>
                                        <option value="fra">France</option>
                                        <option value="ind">India</option>
                                        <option value="aus">Australia</option>
                                        <option value="bra">Brazil</option>
                                        <option value="cana">Canada</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <label for="postcode">Postcode/Zip</label>
                                    <input type="text" class="form-control" id="postcode" value="">
                                </div>
                                <div class="col-md-12 mb-4">
                                    <label for="order-notes">Order Notes</label>
                                    <textarea class="form-control" id="order-notes" cols="30" rows="10" placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn alazea-btn">Place Order</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="checkout-content">
                        <h5 class="title--">Your Order</h5>
                        <div class="products">
                            <div class="products-data">
                                <h5>Products:</h5>
                                @php($cart = $cart ?? null)
                                @if(($cart?->items ?? collect())->count() === 0)
                                    <p class="text-muted">Your cart is empty.</p>
                                @endif
                                @if(($cart?->items ?? collect())->count())
                                    @each('urbangreen.partials.checkout-row', ($cart?->items ?? []), 'item')
                                @endif
                            </div>
                        </div>
                        @php($subtotal = $subtotal ?? ($cart?->items?->sum(fn($i) => (($i->product->sale_price && $i->product->sale_price < $i->product->price) ? $i->product->sale_price : $i->product->price) * $i->quantity) ?? 0))
                        @php($shipping = $shipping ?? 0)
                        @php($total = $total ?? ($subtotal + $shipping))
                        <div class="subtotal d-flex justify-content-between align-items-center">
                            <h5>Subtotal</h5>
                            <h5>${{ number_format($subtotal, 2) }}</h5>
                        </div>
                        <div class="shipping d-flex justify-content-between align-items-center">
                            <h5>Shipping</h5>
                            <h5>${{ number_format($shipping, 2) }}</h5>
                        </div>
                        <div class="order-total d-flex justify-content-between align-items-center">
                            <h5>Order Total</h5>
                            <h5>${{ number_format($total, 2) }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ##### Checkout Area End ##### -->
@endsection
