@extends('urbangreen.layouts.main')

@section('content')
<!-- ##### Breadcrumb Area Start ##### -->
<div class="breadcrumb-area">
    <div class="top-breadcrumb-area bg-img bg-overlay d-flex align-items-center justify-content-center" style="background-image: url({{ asset('urbangreen/img/bg-img/24.jpg') }});">
        <h2>My Favorite Products</h2>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('front.home', [], false) }}"><i class="fa fa-home"></i> Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">My Favorites</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>
<!-- ##### Breadcrumb Area End ##### -->

<!-- ##### Favorites Area Start ##### -->
<section class="alazea-portfolio-area section-padding-100-0">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-heading text-center">
                    <h2>My Favorites</h2>
                    <p>Products you've marked as favorites for easy access</p>
                </div>
            </div>
        </div>

        <!-- Action Bar -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-2">
                        <span class="badge badge-primary" style="font-size: 1rem; padding: 8px 16px;">
                            {{ $favoriteProducts->count() }} Favorite{{ $favoriteProducts->count() !== 1 ? 's' : '' }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <a href="{{ route('front.notifications.index') }}" class="btn alazea-btn-outline mr-2">
                            <i class="fa fa-bell mr-1"></i>View Notifications
                        </a>
                        <a href="{{ route('front.maintenance') }}" class="btn alazea-btn">
                            <i class="fa fa-plus mr-1"></i>Add More
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if($favoriteProducts->count() > 0)
            <div class="row">
                @foreach($favoriteProducts as $product)
                    <div class="col-12 col-md-6 col-lg-4 mb-4">
                        <div class="single-portfolio-item card h-100" style="border: none; border-radius: 15px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: all 0.3s ease;">
                            <!-- Product Image -->
                            <div class="position-relative" style="height: 250px; overflow: hidden;">
                                <img src="{{ $product->primary_image_url ?? asset('urbangreen/img/bg-img/9.jpg') }}" 
                                     alt="{{ $product->name }}" 
                                     class="card-img-top" 
                                     style="height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                                     onerror="this.src='{{ asset('urbangreen/img/bg-img/9.jpg') }}';">
                                
                                <!-- Favorite Heart -->
                                <div class="position-absolute top-0 end-0 m-3">
                                    <button 
                                        onclick="toggleFavorite({{ $product->id }})"
                                        class="btn btn-link p-0 favorite-btn"
                                        style="color: #FF6347; font-size: 1.5rem; text-decoration: none; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5));"
                                    >
                                        <i class="fa fa-heart"></i>
                                    </button>
                                </div>

                                <!-- Category Badge -->
                                <div class="position-absolute bottom-0 start-0 m-3">
                                    <span class="badge" style="background: rgba(255, 99, 71, 0.9); font-size: 0.8rem; padding: 8px 12px; border-radius: 20px;">
                                        {{ $product->category->name ?? 'N/A' }}
                                    </span>
                                </div>
                            </div>

                            <div class="card-body p-4">
                                <!-- Product Name -->
                                <h5 class="card-title mb-3" style="color: #333; font-weight: 600; line-height: 1.3;">
                                    {{ $product->name }}
                                </h5>

                                <!-- Product Info -->
                                <div class="product-info mb-3">
                                    @if($product->price)
                                        <div class="mb-2">
                                            <span class="text-muted">Price: </span>
                                            <span class="font-weight-bold" style="color: #FF6347;">{{ $product->price }}€</span>
                                        </div>
                                    @endif
                                    
                                    @if($product->maintenance)
                                        <div class="mb-2">
                                            <span class="badge badge-success">
                                                <i class="fa fa-wrench mr-1"></i>Maintenance Available
                                            </span>
                                        </div>
                                    @endif

                                    @if($product->notifications->count() > 0)
                                        <div class="mb-2">
                                            <span class="badge badge-info">
                                                <i class="fa fa-bell mr-1"></i>{{ $product->notifications->count() }} Notification{{ $product->notifications->count() !== 1 ? 's' : '' }}
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex justify-content-between align-items-center">
                                    @if($product->maintenance)
                                        <a href="{{ route('front.maintenance.show', $product->id) }}" 
                                           class="btn alazea-btn btn-sm"
                                           style="flex: 1; margin-right: 10px;">
                                            <i class="fa fa-eye mr-1"></i>View Maintenance
                                        </a>
                                    @else
                                        <span class="text-muted" style="flex: 1;">No maintenance guide</span>
                                    @endif
                                    
                                    <small class="text-muted">
                                        Added {{ \Carbon\Carbon::parse($product->pivot->created_at)->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        @else
            <div class="row">
                <div class="col-12">
                    <div class="text-center py-5">
                        <div class="empty-state" style="max-width: 500px; margin: 0 auto;">
                            <i class="fa fa-heart-o" style="font-size: 4rem; color: #ccc; margin-bottom: 2rem;"></i>
                            <h3 style="color: #333; margin-bottom: 1rem;">No Favorites Yet</h3>
                            <p style="color: #666; margin-bottom: 2rem; line-height: 1.6;">
                                You haven't added any products to your favorites yet. Browse our maintenance guides and click the heart icon to save products you're interested in!
                            </p>
                            <a href="{{ route('front.maintenance') }}" class="btn alazea-btn">
                                <i class="fa fa-search mr-2"></i>Browse Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>
<!-- ##### Favorites Area End ##### -->

<style>
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
}

.card:hover .card-img-top {
    transform: scale(1.05);
}

.favorite-btn:hover {
    color: #ff4500 !important;
    transform: scale(1.1);
}

.badge {
    font-size: 0.8rem;
    padding: 6px 12px;
    border-radius: 15px;
}

.badge-primary {
    background-color: #FF6347;
    color: white;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.badge-info {
    background-color: #17a2b8;
    color: white;
}

.empty-state {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 3rem 2rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        align-items: stretch !important;
    }
    
    .d-flex.justify-content-between > div {
        margin-bottom: 1rem;
        text-align: center;
    }
    
    .card {
        margin-bottom: 1.5rem;
    }
}
</style>

<script>
function toggleFavorite(productId) {
    const btn = event.target.closest('.favorite-btn');
    const icon = btn.querySelector('i');
    
    // Disable button during request
    btn.disabled = true;
    
    fetch(`/favorites/toggle/${productId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (!data.is_favorited) {
                // Product was removed from favorites, remove the card
                btn.closest('.col-12').remove();
                
                // Update favorites count
                const badge = document.querySelector('.badge-primary');
                if (badge) {
                    const currentCount = parseInt(badge.textContent.match(/\d+/)[0]);
                    const newCount = currentCount - 1;
                    badge.textContent = `${newCount} Favorite${newCount !== 1 ? 's' : ''}`;
                    
                    // If no favorites left, reload page to show empty state
                    if (newCount === 0) {
                        location.reload();
                    }
                }
            }
            
            // Show success message
            showNotification(data.message, 'success');
        } else {
            showNotification('An error occurred', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred', 'error');
    })
    .finally(() => {
        btn.disabled = false;
    });
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
    `;
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>
@endsection