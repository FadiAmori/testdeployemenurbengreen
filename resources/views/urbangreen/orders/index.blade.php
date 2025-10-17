@extends('urbangreen.layouts.main')
@section('content')
<div class="breadcrumb-area">
  <div class="top-breadcrumb-area bg-img bg-overlay d-flex align-items-center justify-content-center" style="background-image: url({{ asset('urbangreen/img/bg-img/24.jpg') }});">
    <h2>My Orders</h2>
  </div>
  <div class="container">
    <div class="row"><div class="col-12">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('front.home', [], false) }}"><i class="fa fa-home"></i> Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">My Orders</li>
        </ol>
      </nav>
    </div></div>
  </div>
</div>

<section class="section-padding-100">
  <div class="container">
    @if(session('status'))
      <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @forelse($orders as $order)
      <div class="card mb-3">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
              <strong>Order #{{ $order->id }}</strong>
              <span class="text-muted">• {{ $order->order_date->format('M d, Y') }}</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="badge {{ $order->status === 'pending' ? 'badge-warning' : ($order->status === 'confirmed' ? 'badge-success' : 'badge-primary') }}">{{ ucfirst($order->status) }}</span>
              <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#orderDetails{{ $order->id }}">Details</button>
              @if($order->status === 'confirmed')
                <form method="POST" action="{{ route('front.orders.delivered', $order) }}" onsubmit="return confirm('Confirm delivery received?');">
                  @csrf
                  <button class="btn btn-sm btn-success">Confirm delivery</button>
                </form>
              @endif
            </div>
          </div>
          <ul class="list-unstyled mb-2">
            @foreach($order->items as $item)
              <li class="d-flex justify-content-between align-items-center">
                <span>{{ $item->product->name }} × {{ $item->quantity }}</span>
                <span>${{ number_format($item->price_at_purchase * $item->quantity, 2) }}</span>
              </li>
            @endforeach
          </ul>
          <div class="d-flex justify-content-between">
            <small class="text-muted">Ship to: {{ $order->shipping_address }}</small>
            <strong>Total: ${{ number_format($order->total_price, 2) }}</strong>
          </div>
        </div>
      </div>

      <!-- Details Modal -->
      <div class="modal fade" id="orderDetails{{ $order->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Order #{{ $order->id }} details</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-6">
                  <h6 class="mb-2">Items</h6>
                  <ul class="list-unstyled">
                    @foreach($order->items as $item)
                      <li class="d-flex align-items-center mb-2">
                        <img src="{{ $item->product->primary_image_url }}" alt="" style="width:40px;height:40px;border-radius:6px;object-fit:cover;" class="mr-2">
                        <span>{{ $item->product->name }} × {{ $item->quantity }}</span>
                        <span class="ms-auto">${{ number_format($item->price_at_purchase * $item->quantity, 2) }}</span>
                      </li>
                    @endforeach
                  </ul>
                </div>
                <div class="col-md-6">
                  <h6 class="mb-2">Shipping</h6>
                  <p class="mb-1"><strong>Address:</strong> {{ $order->shipping_address }}</p>
                  <p class="mb-3"><strong>Date:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</p>
                  <div class="d-flex justify-content-between">
                    <span>Subtotal</span>
                    <strong>${{ number_format($order->total_price, 2) }}</strong>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    @empty
      <div class="alert alert-info">You have no orders yet.</div>
    @endforelse
  </div>
</section>
@endsection
