@extends('urbangreen.layouts.main')

@section('content')
<!-- ##### Breadcrumb Area Start ##### -->
<div class="breadcrumb-area">
    <div class="top-breadcrumb-area bg-img bg-overlay d-flex align-items-center justify-content-center" style="background-image: url({{ asset('urbangreen/img/bg-img/24.jpg') }});">
        <h2>{{ $category->name }}</h2>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('front.home', [], false) }}"><i class="fa fa-home"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('front.maintenance') }}">Maintenance Categories</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- ##### Breadcrumb Area End ##### -->

<!-- ##### Products Area Start ##### -->
<section class="alazea-portfolio-area section-padding-100-0">
    <div class="container">
        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
        <!-- Warning Message -->
        @if (isset($message))
            <div class="alert alert-warning">
                {{ $message }}
            </div>
        @endif
        <!-- Material Filter Form -->
        <div class="row mb-4">
            <div class="col-12 col-md-4">
                <form method="GET" action="{{ route('front.maintenance.category', ['categoryId' => $category->id]) }}">
                    <div class="input-group">
                        <input type="text" name="material" class="form-control" placeholder="Filter by material..." value="{{ request()->query('material') }}">
                        <button type="submit" class="btn alazea-btn">Filter</button>
                    </div>
                </form>
            </div>
            <div class="col-12 col-md-8 text-end">
                <a href="{{ route('front.maintenance') }}" class="btn alazea-btn">Back to Categories</a>
            </div>
        </div>
        <!-- Products List -->
        <div class="row">
            @forelse ($category->products as $product)
                <div class="col-12 col-md-4 mb-4">
                    <div class="single-portfolio-item card h-100" @if ($product->maintenance) onclick="window.location='{{ route('front.maintenance.show', ['productId' => $product->id]) }}'" style="cursor: pointer;" @endif>
                        <div class="card-body text-center">
                            <img src="{{ $product->primary_image_url ?? asset('urbangreen/img/bg-img/9.jpg') }}" alt="{{ $product->name }}" class="card-img-top img-fluid" style="max-height: 200px; object-fit: cover;">
                            <h5 class="card-title mt-3">{{ $product->name }}</h5>
                            @if ($product->maintenance)
                                <a href="{{ route('front.maintenance.show', ['productId' => $product->id]) }}" class="btn alazea-btn btn-sm">View Maintenance</a>
                            @else
                                <p class="text-muted">No maintenance available</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-0">No products found in this category.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
<!-- ##### Products Area End ##### -->
@endsection