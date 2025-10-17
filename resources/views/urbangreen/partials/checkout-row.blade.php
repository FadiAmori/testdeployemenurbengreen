@php($p = $item->product)
@php($unit = ($p->sale_price && $p->sale_price < $p->price) ? $p->sale_price : $p->price)
<div class="single-products d-flex justify-content-between align-items-center">
  <p>{{ $p->name }} × {{ $item->quantity }}</p>
  <h5>${{ number_format($unit * $item->quantity, 2) }}</h5>
  </div>

