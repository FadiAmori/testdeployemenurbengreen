@php(
    $imageUrl = $product->primary_image_url ?? asset('urbangreen/img/bg-img/24.jpg')
)
@php(
    $productUrl = route('front.shop.show', [$product->id], false)
)
@php(
    $favoriteIds = $favoriteIds ?? (auth()->check() ? auth()->user()->favoriteProducts()->pluck('product_id')->toArray() : [])
)
@php($isFav = in_array($product->id, $favoriteIds))

<div class="col-12 col-sm-6 col-lg-4">
  <div class="single-product-area mb-50">
    <div class="product-img">
      <a href="{{ $productUrl }}"><img src="{{ $imageUrl }}" alt="{{ $product->name }}" onerror="this.onerror=null;this.src='{{ asset('urbangreen/img/bg-img/9.jpg') }}';"></a>
      @if($product->sale_price && $product->sale_price < $product->price)
        <div class="product-tag sale-tag"><a href="#">Sale</a></div>
      @elseif($product->is_featured)
        <div class="product-tag hot"><a href="#">Hot</a></div>
      @endif
      <div class="product-meta d-flex">
        <a href="#" class="wishlist-btn js-favorite {{ $isFav ? 'active' : '' }}" data-product-id="{{ $product->id }}" title="Add to favorites"><i class="icon_heart_alt"></i></a>
        <a href="#" class="add-to-cart-btn js-add-to-cart" data-product-id="{{ $product->id }}">Add to cart</a>
        <a href="{{ route('front.shop.quick-view', [$product->id], false) }}" class="compare-btn" data-product="{{ $product->id }}"><i class="fa fa-eye"></i></a>
      </div>
    </div>
    <div class="product-info mt-15 text-center">
      <a href="{{ $productUrl }}"><p>{{ $product->name }}</p></a>
      <h6 class="mb-0">
        @if($product->sale_price && $product->sale_price < $product->price)
          <span class="text-muted"><del>${{ number_format($product->price, 2) }}</del></span>
          <span class="ml-1 text-success">${{ number_format($product->sale_price, 2) }}</span>
        @else
          ${{ number_format($product->price, 2) }}
        @endif
      </h6>
    </div>
  </div>
</div>
