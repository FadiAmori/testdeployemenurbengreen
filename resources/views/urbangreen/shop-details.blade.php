@extends('urbangreen.layouts.main')
@section('content')
<div class="breadcrumb-area">
    <div class="top-breadcrumb-area bg-img bg-overlay d-flex align-items-center justify-content-center" style="background-image: url({{ asset('urbangreen/img/bg-img/24.jpg') }});">
        <h2>{{ strtoupper($product->name) }}</h2>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('front.home', [], false) }}"><i class="fa fa-home"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('front.shop', [], false) }}">Shop</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="single_product_details_area mb-50">
    <div class="produts-details--content mb-50">
        <div class="container">
            <div class="row justify-content-between">
                @php
                    $images = $product->images->count() ? $product->images : collect([$product->primaryImage])->filter();
                    $resolveImageUrl = fn ($path, $fallback = 'urbangreen/img/bg-img/49.jpg') => \App\Models\Shop\Product::resolveImageUrl($path, $fallback);
                    $primaryImage = $resolveImageUrl(optional($product->primaryImage)->path, 'urbangreen/img/bg-img/49.jpg');
                @endphp
                <div class="col-12 col-md-6 col-lg-5">
                    <div class="single_product_thumb">
                        <img class="d-block w-100 mb-3" src="{{ $primaryImage }}" alt="{{ $product->name }}">
                        @if($images->count() > 1)
                            <div class="d-flex flex-wrap">
                                @foreach($images as $image)
                                    @php($thumb = $resolveImageUrl($image->path, 'urbangreen/img/bg-img/49.jpg'))
                                    <a class="product-img mr-2 mb-2" href="{{ $thumb }}" title="{{ $product->name }}">
                                        <img style="width: 90px; height: 90px; object-fit: cover;" src="{{ $thumb }}" alt="{{ $product->name }} thumbnail">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="single_product_desc">
                        <span class="mb-2 d-inline-block text-muted">{{ optional($product->category)->name }}</span>
                        <h4 class="title">{{ $product->name }}</h4>
                        <div class="d-flex align-items-center mb-3">
                            <h4 class="price mb-0">
                                @if($product->sale_price && $product->sale_price < $product->price)
                                    <span class="text-muted"><del>${{ number_format($product->price, 2) }}</del></span>
                                    <span class="ml-2 text-success">${{ number_format($product->sale_price, 2) }}</span>
                                @else
                                    ${{ number_format($product->price, 2) }}
                                @endif
                            </h4>
                            @if($product->sale_price && $product->sale_price < $product->price)
                                <span class="badge badge-success ml-3">On Sale</span>
                            @endif
                        </div>
                        <div class="mb-3">
                            <span class="badge badge-{{ $product->availability === 'out_of_stock' ? 'danger' : 'success' }}">
                                {{ ucwords(str_replace('_', ' ', $product->availability)) }}
                            </span>
                            <span class="badge badge-light">Stock: {{ $product->stock }}</span>
                        </div>
                        <div class="short_overview mb-4">
                            <p>{{ $product->short_description ?: \Illuminate\Support\Str::limit(strip_tags($product->description), 180) }}</p>
                        </div>
                        <div class="cart--area d-flex flex-wrap align-items-center">
                            <form class="cart clearfix d-flex align-items-center" method="post">
                                <div class="quantity mr-3">
                                    <input type="number" class="qty-text" step="1" min="1" name="quantity" value="1">
                                </div>
                                <button type="button" class="btn alazea-btn" onclick="window.location='{{ route('front.cart', [], false) }}'">Add to cart</button>
                            </form>
                            <div class="wishlist-compare d-flex align-items-center">
                                <a href="#" class="wishlist-btn ml-3"><i class="icon_heart_alt"></i></a>
                                <a href="#" class="compare-btn ml-3"><i class="arrow_left-right_alt"></i></a>
                            </div>
                        </div>
                        <div class="products--meta">
                            <p><span>SKU:</span> {{ $product->sku }}</p>
                            <p><span>Category:</span> {{ optional($product->category)->name ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-12">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#description" role="tab">Description</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#additional" role="tab">Additional Info</a></li>
                    </ul>
                    <div class="tab-content pt-3">
                        <div role="tabpanel" class="tab-pane fade show active" id="description">
                            <div class="description_area">
                                {!! $product->description ?? '<p>No description provided yet.</p>' !!}
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane fade" id="additional">
                            <div class="additional_info_area">
                                <p><span>Status:</span> {{ ucfirst($product->status) }}</p>
                                <p><span>Availability:</span> {{ ucwords(str_replace('_', ' ', $product->availability)) }}</p>
                                <p><span>Weight:</span> {{ $product->weight ? $product->weight . ' kg' : '—' }}</p>
                                @if($product->attributes)
                                    @foreach($product->attributes as $key => $value)
                                        <p><span>{{ ucfirst(str_replace('_',' ', $key)) }}:</span> {{ is_array($value) ? implode(', ', $value) : $value }}</p>
                                    @endforeach
                                @else
                                    <p class="text-muted mb-0">No extra information provided.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="related-products-area section-padding-0-100">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-heading text-center">
                    <h2>Related products</h2>
                    <p>You might also like these items</p>
                </div>
            </div>
        </div>
        <div class="row">
            @forelse($relatedProducts as $related)
                @php($image = \App\Models\Shop\Product::resolveImageUrl(optional($related->primaryImage)->path))
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="single-product-area mb-50">
                        <div class="product-img">
                            <a href="{{ route('front.shop.show', [$related->id], false) }}"><img src="{{ $image }}" alt="{{ $related->name }}"></a>
                            <div class="product-meta d-flex">
                                <a href="#" class="wishlist-btn"><i class="icon_heart_alt"></i></a>
                                <a href="{{ route('front.cart', [], false) }}" class="add-to-cart-btn">Add to cart</a>
                                <a href="#" class="compare-btn"><i class="arrow_left-right_alt"></i></a>
                            </div>
                        </div>
                        <div class="product-info mt-15 text-center">
                            <a href="{{ route('front.shop.show', [$related->id], false) }}"><p>{{ $related->name }}</p></a>
                            <h6>${{ number_format($related->sale_price ?? $related->price, 2) }}</h6>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <p class="text-center text-muted">No related products yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
