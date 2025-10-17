@extends('urbangreen.layouts.main')

@section('content')
<!-- ##### Breadcrumb Area Start ##### -->
<div class="breadcrumb-area">
    <div class="top-breadcrumb-area bg-img bg-overlay d-flex align-items-center justify-content-center" style="background-image: url({{ asset('urbangreen/img/bg-img/24.jpg') }});">
        <h2>My Product Notifications</h2>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('front.home', [], false) }}"><i class="fa fa-home"></i> Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('front.favorites.index') }}">My Favorites</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Notifications</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- ##### Breadcrumb Area End ##### -->

<!-- ##### Notifications Area Start ##### -->
<section class="alazea-portfolio-area section-padding-100-0">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-heading text-center">
                    <h2>Product Notifications</h2>
                    <p>Stay updated with notifications from your favorite products</p>
                </div>
            </div>
        </div>

        @if($notifications->count() > 0)
            <div class="row">
                @foreach($notifications as $notification)
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="card h-100" style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); border: none; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                            <!-- Product Image -->
                            <div class="position-relative" style="height: 200px; overflow: hidden;">
                                <img src="{{ $notification['product']->primary_image_url ?? asset('urbangreen/img/bg-img/9.jpg') }}" 
                                     alt="{{ $notification['product']->name }}" 
                                     class="card-img-top" 
                                     style="height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                                     onerror="this.src='{{ asset('urbangreen/img/bg-img/9.jpg') }}';">
                                
                                <!-- Category Badge -->
                                <div class="position-absolute top-0 start-0 m-3">
                                    <span class="badge" style="background: rgba(255, 99, 71, 0.9); font-size: 0.8rem; padding: 8px 12px; border-radius: 20px;">
                                        {{ $notification['product']->category->name ?? 'N/A' }}
                                    </span>
                                </div>

                                <!-- Favorite Heart -->
                                <div class="position-absolute top-0 end-0 m-3">
                                    <i class="fa fa-heart text-danger" style="font-size: 1.5rem; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));"></i>
                                </div>
                            </div>

                            <div class="card-body p-4" style="color: #fff;">
                                <!-- Product Name -->
                                <h5 class="card-title mb-3" style="color: #FF6347; font-weight: 600; line-height: 1.3;">
                                    {{ $notification['product']->name }}
                                </h5>

                                <!-- Notification Content -->
                                <div class="notification-content mb-3">
                                    <h6 class="mb-2" style="color: #FFE4B5; font-size: 1rem; font-weight: 500;">
                                        <i class="fa fa-bell mr-2"></i>{{ $notification['notification']->name }}
                                    </h6>
                                    @if($notification['notification']->description)
                                        <p class="mb-0" style="color: #cccccc; font-size: 0.9rem; line-height: 1.5;">
                                            {{ Str::limit($notification['notification']->description, 100) }}
                                        </p>
                                    @endif
                                </div>

                                <!-- Timestamp -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted" style="color: #999 !important;">
                                        <i class="fa fa-clock-o mr-1"></i>
                                        {{ \Carbon\Carbon::parse($notification['created_at'])->diffForHumans() }}
                                    </small>
                                    
                                    <!-- Action Button -->
                                    @if($notification['product']->maintenance)
                                        <a href="{{ route('front.maintenance.show', $notification['product']->id) }}" 
                                           class="btn btn-sm"
                                           style="background: #FF6347; color: white; border: none; border-radius: 25px; padding: 8px 20px; transition: all 0.3s ease;">
                                            <i class="fa fa-eye mr-1"></i>View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Navigation -->
            <div class="row mt-5">
                <div class="col-12 text-center">
                    <a href="{{ route('front.favorites.index') }}" class="btn alazea-btn mr-3">
                        <i class="fa fa-heart mr-2"></i>View All Favorites
                    </a>
                    <a href="{{ route('front.maintenance') }}" class="btn alazea-btn-outline">
                        <i class="fa fa-search mr-2"></i>Discover More Products
                    </a>
                </div>
            </div>

        @else
            <div class="row">
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="empty-state" style="max-width: 500px; margin: 0 auto;">
                            <i class="fa fa-bell-slash-o" style="font-size: 4rem; color: #ccc; margin-bottom: 2rem;"></i>
                            <h3 style="color: #333; margin-bottom: 1rem;">No Notifications Yet</h3>
                            <p style="color: #666; margin-bottom: 2rem; line-height: 1.6;">
                                You don't have any product notifications yet. Start by adding products to your favorites to receive updates!
                            </p>
                            <div>
                                <a href="{{ route('front.maintenance') }}" class="btn alazea-btn mr-3">
                                    <i class="fa fa-search mr-2"></i>Browse Products
                                </a>
                                @if($favoriteProducts->count() > 0)
                                    <a href="{{ route('front.favorites.index') }}" class="btn alazea-btn-outline">
                                        <i class="fa fa-heart mr-2"></i>My Favorites ({{ $favoriteProducts->count() }})
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
<!-- ##### Notifications Area End ##### -->

<style>
.card:hover {
    transform: translateY(-5px);
    transition: all 0.3s ease;
}

.card:hover .card-img-top {
    transform: scale(1.05);
}

.notification-content {
    border-left: 3px solid #FF6347;
    padding-left: 15px;
    margin-left: 5px;
}

.btn:hover {
    transform: scale(1.05);
    box-shadow: 0 5px 15px rgba(255, 99, 71, 0.3);
}

.empty-state {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 3rem 2rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .card {
        margin-bottom: 1.5rem;
    }
    
    .btn {
        font-size: 0.9rem;
        padding: 10px 20px;
    }
}
</style>
@endsection