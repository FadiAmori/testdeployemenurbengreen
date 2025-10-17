@extends('urbangreen.layouts.main')
@section('content')
<div class="breadcrumb-area">
    <div class="top-breadcrumb-area bg-img bg-overlay d-flex align-items-center justify-content-center" style="background-image: url({{ asset('urbangreen/img/bg-img/24.jpg') }});">
        <h2>Shop</h2>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('front.home', [], false) }}"><i class="fa fa-home"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Shop</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="shop-page section-padding-0-100">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="shop-sorting-data d-flex flex-wrap align-items-center justify-content-between">
                    <div class="shop-page-count">
                        @if ($products->total() > 0)
                            <p>Showing {{ $products->firstItem() }}–{{ $products->lastItem() }} of {{ $products->total() }} results</p>
                        @else
                            <p>No results found</p>
                        @endif
                    </div>
                    <div class="search_by_terms">
                        <form method="GET" action="{{ route('front.shop', [], false) }}" class="form-inline">
                            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="form-control mr-2 mb-2" placeholder="Search products...">
                            <select name="sort" class="custom-select widget-title mr-2 mb-2">
                                <option value="" {{ empty($filters['sort']) ? 'selected' : '' }}>Sort by Featured</option>
                                <option value="newest" {{ ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' }}>Newest</option>
                                <option value="price-asc" {{ ($filters['sort'] ?? '') === 'price-asc' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price-desc" {{ ($filters['sort'] ?? '') === 'price-desc' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="name-asc" {{ ($filters['sort'] ?? '') === 'name-asc' ? 'selected' : '' }}>Name: A → Z</option>
                                <option value="name-desc" {{ ($filters['sort'] ?? '') === 'name-desc' ? 'selected' : '' }}>Name: Z → A</option>
                            </select>
                            <select name="per_page" class="custom-select widget-title mr-2 mb-2">
                                @foreach ([9, 12, 18, 24] as $size)
                                    <option value="{{ $size }}" {{ (int)($filters['per_page'] ?? 9) === $size ? 'selected' : '' }}>Show: {{ $size }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn alazea-btn mb-2">Apply</button>
                            @if(!empty($filters['category']))
                                <input type="hidden" name="category" value="{{ $filters['category'] }}">
                            @endif
                            @if(!empty($filters['subcategory']))
                                <input type="hidden" name="subcategory" value="{{ $filters['subcategory'] }}">
                            @endif
                            @if(!empty($filters['price_min']))
                                <input type="hidden" name="price_min" value="{{ $filters['price_min'] }}">
                            @endif
                            @if(!empty($filters['price_max']))
                                <input type="hidden" name="price_max" value="{{ $filters['price_max'] }}">
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                @include('shop.ai-chat')
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-md-4 col-lg-3">
                <div class="shop-sidebar-area">
                    <div class="shop-widget price mb-50">
                        <h4 class="widget-title">Prices</h4>
                        <div class="widget-desc">
                            <form method="GET" action="{{ route('front.shop', [], false) }}">
                                @foreach(['search','sort','per_page','category','subcategory'] as $keep)
                                    @if(!empty($filters[$keep]))
                                        <input type="hidden" name="{{ $keep }}" value="{{ $filters[$keep] }}">
                                    @endif
                                @endforeach
                                <div class="slider-range">
                                    <div class="range-price mb-3">
                                        Price: ${{ number_format($filters['price_min'] ?? floor($priceRange->min_price ?? 0), 0) }} - ${{ number_format($filters['price_max'] ?? ceil($priceRange->max_price ?? 0), 0) }}
                                    </div>
                                    <div class="form-row">
                                        <div class="col">
                                            <input type="number" name="price_min" min="0" step="1" class="form-control" value="{{ $filters['price_min'] ?? floor($priceRange->min_price ?? 0) }}" placeholder="Min">
                                        </div>
                                        <div class="col">
                                            <input type="number" name="price_max" min="0" step="1" class="form-control" value="{{ $filters['price_max'] ?? ceil($priceRange->max_price ?? 0) }}" placeholder="Max">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-sm alazea-btn mt-3">Filter</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="shop-widget catagory mb-50">
                        <h4 class="widget-title">Categories</h4>
                        <div class="widget-desc">
                            <ul class="list-unstyled mb-0" id="shop-categories">
                                @php
                                    $allQuery = array_filter(array_merge($filters, ['category' => null, 'subcategory' => null, 'page' => null]), function ($value) {
                                        return $value !== null && $value !== '';
                                    });
                                @endphp
                                <li class="mb-2">
                                    <a class="d-flex justify-content-between align-items-center {{ empty($filters['category']) ? 'text-success font-weight-bold' : '' }}" href="{{ route('front.shop', $allQuery, false) }}">
                                        <span>All products</span>
                                        <span class="text-muted">({{ $products->total() }})</span>
                                    </a>
                                </li>
                                @foreach ($categories as $category)
                                    @include('urbangreen.partials.category-item', ['category' => $category, 'filters' => $filters, 'level' => 0])
                                @endforeach
                            </ul>
                        </div>
                    </div>

                </div>
            </div>

                    <div class="col-12 col-md-8 col-lg-9">
                <div class="row" id="shop-products">
                    @php($favoriteIds = auth()->check() ? auth()->user()->favoriteProducts()->pluck('product_id')->toArray() : [])
                    @if(!$products->count())
                        <div class="col-12">
                            <div class="alert alert-info">No products match your filters. Try adjusting your search.</div>
                        </div>
                    @endif
                    @each('urbangreen.partials.product-card', $products, 'product')
                </div>

                <div class="row" id="shop-pagination">
                    <div class="col-12">
                        {{ $products->withQueryString()->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<style>
    .category-link { color:#111; padding:.35rem .25rem; border-radius:6px; transition: background-color .15s ease, color .15s ease; }
    .category-link:hover{ background:rgba(112, 199, 69, .08); color:#111; }
    .category-link.active{ color:#70c745; font-weight:600; background:rgba(112, 199, 69, .12); }
    .product-meta .wishlist-btn.active i{ color:#e11d48; }
    .product-meta .wishlist-btn i{ transition: color .15s ease; }
    .product-meta .add-to-cart-btn.disabled{ opacity:.6; pointer-events:none; }
    .alert-fixed{ position:fixed; top:90px; right:20px; z-index:9999; }
    .shop-sorting-data .form-control{ border-radius: 4px; }
    .shop-sorting-data .custom-select{ border-radius: 4px; }
</style>
<script>
    (function(){
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const showToast = (msg, ok=true) => {
            const el = document.createElement('div');
            el.className = 'alert alert-' + (ok ? 'success' : 'danger') + ' alert-fixed';
            el.textContent = msg;
            document.body.appendChild(el);
            setTimeout(()=> el.remove(), 2500);
        };

        function bindShopEvents(root=document){
            root.querySelectorAll('.js-favorite').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    const productId = btn.dataset.productId;
                    try{
                        const res = await fetch(`{{ url('/favorites/toggle') }}/${productId}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        });
                        if(res.status === 401){ window.location.href = `{{ route('login') }}`; return; }
                        const data = await res.json();
                        if(data.success){
                            btn.classList.toggle('active', !!data.is_favorited);
                            showToast(data.message, true);
                        }else{ showToast('Something went wrong', false); }
                    }catch(err){ showToast('Network error', false); }
                });
            });

            root.querySelectorAll('.js-add-to-cart').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    if(btn.classList.contains('disabled')) return;
                    btn.classList.add('disabled');
                    const productId = btn.dataset.productId;
                    try{
                        const res = await fetch(`{{ url('/cart/add') }}/${productId}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                        });
                        if(res.status === 401){ window.location.href = `{{ route('login') }}`; return; }
                        showToast('Added to cart');
                    }catch(err){ showToast('Network error', false); }
                    finally{ btn.classList.remove('disabled'); }
                });
            });

            // Intercept category clicks (PJAX)
            root.querySelectorAll('#shop-categories a, .shop-sorting-data a.page-link').forEach(a => {
                a.addEventListener('click', (e) => {
                    const href = a.getAttribute('href');
                    if(!href) return;
                    e.preventDefault();
                    loadShop(href);
                });
            });

            // Intercept pagination links
            root.querySelectorAll('#shop-pagination a.page-link').forEach(a => {
                a.addEventListener('click', (e) => {
                    const href = a.getAttribute('href');
                    if(!href) return;
                    e.preventDefault();
                    loadShop(href);
                });
            });

            // Intercept the top search/sort form
            const form = root.querySelector('.search_by_terms form');
            if(form){
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    const params = new URLSearchParams(new FormData(form));
                    const url = `{{ route('front.shop', [], false) }}?${params.toString()}`;
                    loadShop(url);
                });
            }
        }

        async function loadShop(url){
            try{
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }});
                const html = await res.text();
                const tmp = document.createElement('div');
                tmp.innerHTML = html;
                const products = tmp.querySelector('#shop-products');
                const categories = tmp.querySelector('#shop-categories');
                const pagination = tmp.querySelector('#shop-pagination');
                if(products && categories){
                    document.querySelector('#shop-products').innerHTML = products.innerHTML;
                    document.querySelector('#shop-categories').innerHTML = categories.innerHTML;
                    if(pagination && document.querySelector('#shop-pagination')){
                        document.querySelector('#shop-pagination').innerHTML = pagination.innerHTML;
                    }
                    history.pushState({}, '', url);
                    // Rebind events on the swapped nodes
                    bindShopEvents(document);
                } else {
                    // Fallback: if selectors missing, fallback to navigation
                    window.location.href = url;
                }
            }catch(err){
                window.location.href = url;
            }
        }

        bindShopEvents(document);
    })();
</script>
@endsection
