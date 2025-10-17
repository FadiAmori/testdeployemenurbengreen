@extends('urbangreen.layouts.main')

@section('content')
<!-- ##### Breadcrumb Area Start ##### -->
<div class="breadcrumb-area">
    <!-- Top Breadcrumb Area -->
    <div class="top-breadcrumb-area bg-img bg-overlay d-flex align-items-center justify-content-center" style="background-image: url({{ asset('urbangreen/img/bg-img/24.jpg') }});">
        <h2>Maintenance Categories</h2>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('front.home', [], false) }}"><i class="fa fa-home"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Maintenance Categories</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- ##### Breadcrumb Area End ##### -->

<!-- ##### Categories Area Start ##### -->
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
                <form method="GET" action="{{ route('front.maintenance') }}">
                    <div class="input-group">
                        <input type="text" name="material" class="form-control" placeholder="Filter by material..." value="{{ request()->query('material') }}">
                        <button type="submit" class="btn alazea-btn">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- Categories List -->
        <div class="row">
            @forelse ($categories as $category)
                <div class="col-12 col-md-4 mb-4">
                    <div class="single-portfolio-item card h-100" onclick="window.location='{{ route('front.maintenance', ['categoryId' => $category->id]) }}'" style="cursor: pointer;">
                        <div class="card-body text-center">
                            <img src="{{ $category->image_url ?? asset('urbangreen/img/bg-img/9.jpg') }}" alt="{{ $category->name }}" class="card-img-top img-fluid" style="max-height: 200px; object-fit: cover;">
                            <h5 class="card-title mt-3">{{ $category->name }}</h5>
                            <p class="text-muted">
                                {{ $category->products->count() }} Product{{ $category->products->count() === 1 ? '' : 's' }}
                                @if ($category->products->whereNotNull('deleted_at')->count() > 0)
                                    ({{ $category->products->whereNotNull('deleted_at')->count() }} soft-deleted)
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <p class="text-muted mb-0">No categories found.</p>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
<!-- ##### Categories Area End ##### -->
@endsection