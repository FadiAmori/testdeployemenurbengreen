@php($p = $item->product)
@php($unit = ($p->sale_price && $p->sale_price < $p->price) ? $p->sale_price : $p->price)
@php($line = $unit * $item->quantity)
<tr>
  <td class="cart_product_img">
    <a href="{{ route('front.shop.show', [$p->id], false) }}">
      <img src="{{ $p->primary_image_url }}" alt="{{ $p->name }}" style="width:84px;height:84px;object-fit:cover;border-radius:8px;">
    </a>
    <h5 class="mt-2">{{ $p->name }}</h5>
  </td>
  <td class="qty">
    <form method="POST" action="{{ route('front.cart.item.update', $item) }}" class="d-inline-flex align-items-center">
      @csrf
      <div class="quantity">
        <input type="number" class="qty-text" step="1" min="1" max="99" name="quantity" value="{{ $item->quantity }}" onchange="this.form.submit()">
      </div>
    </form>
  </td>
  <td class="price"><span>${{ number_format($unit, 2) }}</span></td>
  <td class="total_price"><span>${{ number_format($line, 2) }}</span></td>
  <td class="action">
    <form method="POST" action="{{ route('front.cart.item.remove', $item) }}">
      @csrf
      @method('DELETE')
      <button class="btn btn-sm btn-link text-danger p-0" title="Remove"><i class="icon_close"></i></button>
    </form>
  </td>
</tr>

