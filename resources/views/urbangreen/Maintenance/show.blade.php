@extends('urbangreen.layouts.main')

@section('content')
<!-- ##### Hero Area Start ##### -->
<div class="hero-area">
    <div class="hero-bg bg-img bg-overlay" style="background-image: url({{ asset('urbangreen/img/bg-img/24.jpg') }}); min-height: 400px;">
        <div class="container h-100">
            <div class="row h-100 align-items-center">
                <div class="col-12">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb bg-transparent mb-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('front.home', [], false) }}" class="text-white">
                                    <i class="fa fa-home"></i> Home
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('front.maintenance') }}" class="text-white">Maintenance</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('front.maintenance.category', ['categoryId' => $maintenance->product->category_id]) }}" class="text-white">
                                    {{ $maintenance->product->category->name }}
                                </a>
                            </li>
                            <li class="breadcrumb-item active text-light" aria-current="page">
                                {{ Str::limit($maintenance->product->name, 30) }}
                            </li>
                        </ol>
                    </nav>
                    
                    <!-- Hero Content -->
                    <div class="hero-content text-center text-white">
                        <div class="hero-title-wrapper mb-4">
                            <h1 class="hero-title mb-3" style="font-size: 3rem; font-weight: 800; text-shadow: 2px 2px 4px rgba(0,0,0,0.8);">
                                {{ $maintenance->product->name }}
                            </h1>
                            <div class="hero-subtitle">
                                <span class="badge badge-hero">
                                    <i class="fa fa-leaf mr-2"></i>{{ $maintenance->product->category->name ?? 'Maintenance Guide' }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="hero-actions">
                            @auth
                                <button 
                                    id="favoriteBtn" 
                                    onclick="toggleFavorite({{ $maintenance->product->id }})"
                                    class="btn btn-hero-favorite mr-3"
                                >
                                    <i id="favoriteIcon" class="fa {{ auth()->user()->favoriteProducts->contains($maintenance->product->id) ? 'fa-heart' : 'fa-heart-o' }} mr-2"></i>
                                    <span id="favoriteText">{{ auth()->user()->favoriteProducts->contains($maintenance->product->id) ? 'Favorited' : 'Add to Favorites' }}</span>
                                </button>
                            @endauth
                            <a href="#maintenance-details" class="btn btn-hero-primary">
                                <i class="fa fa-wrench mr-2"></i>View Guide
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ##### Hero Area End ##### -->

<!-- ##### Maintenance Details Start ##### -->
<section id="maintenance-details" class="maintenance-details-area section-padding-100-0" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
    <div class="container">
        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show custom-alert" role="alert">
                <i class="fa fa-check-circle mr-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show custom-alert" role="alert">
                <i class="fa fa-exclamation-triangle mr-2"></i>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Main Content -->
        <div class="row justify-content-center">
            <div class="col-12">
                <!-- Media Section -->
                <div class="maintenance-media-section mb-5">
                    <div class="row align-items-center">
                        <div class="col-lg-8 mb-4 mb-lg-0">
                            <div class="media-container">
                                @if ($maintenance->video)
                                    <div class="video-wrapper">
                                        <video controls class="maintenance-video">
                                            <source src="{{ Storage::url($maintenance->video) }}" type="video/mp4">
                                            <source src="{{ Storage::url($maintenance->video) }}" type="video/webm">
                                            <p>Your browser does not support the video tag. <a href="{{ Storage::url($maintenance->video) }}">Download the video</a>.</p>
                                        </video>
                                        <div class="video-overlay">
                                            <i class="fa fa-play-circle"></i>
                                        </div>
                                    </div>
                                @elseif ($maintenance->photo)
                                    <div class="image-wrapper">
                                        <img src="{{ Storage::url($maintenance->photo) }}" 
                                             alt="Maintenance Guide for {{ $maintenance->product->name }}" 
                                             class="maintenance-image"
                                             onerror="this.src='{{ asset('urbangreen/img/bg-img/9.jpg') }}'; this.onerror=null;">
                                        <div class="image-overlay">
                                            <i class="fa fa-search-plus"></i>
                                        </div>
                                    </div>
                                @else
                                    <div class="placeholder-wrapper">
                                        <img src="{{ asset('urbangreen/img/bg-img/9.jpg') }}" 
                                             alt="Maintenance Guide Placeholder" 
                                             class="maintenance-image">
                                        <div class="placeholder-overlay">
                                            <i class="fa fa-image"></i>
                                            <p>No media available</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <!-- Quick Info Card -->
                            <div class="quick-info-card">
                                <div class="card-header">
                                    <h4><i class="fa fa-info-circle mr-2"></i>Quick Info</h4>
                                </div>
                                <div class="card-body">
                                    <div class="info-item">
                                        <span class="info-label">Product:</span>
                                        <span class="info-value">{{ $maintenance->product->name }}</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Category:</span>
                                        <span class="info-value badge badge-category">{{ $maintenance->product->category->name ?? 'N/A' }}</span>
                                    </div>
                                    @if($maintenance->product->price)
                                        <div class="info-item">
                                            <span class="info-label">Price:</span>
                                            <span class="info-value text-primary font-weight-bold">{{ $maintenance->product->price }}€</span>
                                        </div>
                                    @endif
                                    <div class="info-item">
                                        <span class="info-label">Status:</span>
                                        <span class="info-value badge badge-success">Available</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Content Sections -->
                <div class="maintenance-content">
                    <!-- Description Section -->
                    <div class="content-section mb-5">
                        <div class="section-header">
                            <h2><i class="fa fa-file-text-o mr-3"></i>Description</h2>
                        </div>
                        <div class="section-content">
                            <div class="description-box">
                                <p>{{ $maintenance->description ?? 'Complete maintenance guide for optimal performance and longevity of your plant care routine.' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Materials Section -->
                    <div class="row mb-5">
                        <!-- Required Materials -->
                        <div class="col-lg-6 mb-4">
                            <div class="materials-section">
                                <div class="section-header">
                                    <h3><i class="fa fa-tools mr-2"></i>Required Materials</h3>
                                    <span class="badge badge-required">Essential</span>
                                </div>
                                <div class="materials-content">
                                    @if ($maintenance->material)
                                        <div class="material-card featured">
                                            <div class="material-image">
                                                <img src="{{ $maintenance->material->primary_image_url ?? asset('urbangreen/img/bg-img/9.jpg') }}" 
                                                     alt="{{ $maintenance->material->name }}"
                                                     onerror="this.src='{{ asset('urbangreen/img/bg-img/9.jpg') }}';">
                                            </div>
                                            <div class="material-info">
                                                <h4>{{ $maintenance->material->name }}</h4>
                                                @if($maintenance->material->price)
                                                    <p class="material-price">{{ $maintenance->material->price }}€</p>
                                                @endif
                                                <span class="material-badge">Required</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="empty-state">
                                            <i class="fa fa-leaf"></i>
                                            <p>No specific materials required</p>
                                            <small>This maintenance can be performed with common gardening tools</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Optional Materials -->
                        <div class="col-lg-6 mb-4">
                            <div class="materials-section">
                                <div class="section-header">
                                    <h3><i class="fa fa-plus-circle mr-2"></i>Optional Products</h3>
                                    <span class="badge badge-optional">Optional</span>
                                </div>
                                <div class="materials-content">
                                    @if($maintenance->optional)
                                        <div class="material-card">
                                            <div class="material-image">
                                                <img src="{{ $maintenance->optional->primary_image_url ?? asset('urbangreen/img/bg-img/9.jpg') }}" 
                                                     alt="{{ $maintenance->optional->name }}"
                                                     onerror="this.src='{{ asset('urbangreen/img/bg-img/9.jpg') }}';">
                                            </div>
                                            <div class="material-info">
                                                <h4>{{ $maintenance->optional->name }}</h4>
                                                @if($maintenance->optional->price)
                                                    <p class="material-price">{{ $maintenance->optional->price }}€</p>
                                                @endif
                                                <span class="material-badge optional">Optional</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="empty-state">
                                            <i class="fa fa-gift"></i>
                                            <p>No optional products</p>
                                            <small>The required materials are sufficient for this maintenance</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Steps Section -->
                    @if ($maintenance->steps && count($maintenance->steps) > 0)
                        <div class="content-section">
                            <div class="section-header">
                                <h2><i class="fa fa-list-ol mr-3"></i>Maintenance Steps</h2>
                                <span class="badge badge-steps">{{ count($maintenance->steps) }} Steps</span>
                            </div>
                            <div class="steps-container">
                                @foreach ($maintenance->steps as $index => $step)
                                    <div class="step-item {{ $loop->last ? 'last' : '' }}">
                                        <div class="step-number">
                                            <span>{{ $index + 1 }}</span>
                                        </div>
                                        <div class="step-content">
                                            <h4 class="step-title">{{ $step['title'] ?? 'Step ' . ($index + 1) }}</h4>
                                            <p class="step-description">{{ $step['description'] ?? 'Follow the instructions carefully for best results.' }}</p>
                                        </div>
                                        @if (!$loop->last)
                                            <div class="step-connector"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="content-section">
                            <div class="section-header">
                                <h2><i class="fa fa-info-circle mr-3"></i>General Maintenance</h2>
                            </div>
                            <div class="general-maintenance">
                                <div class="maintenance-tips">
                                    <div class="tip-card">
                                        <i class="fa fa-lightbulb-o"></i>
                                        <h4>Pro Tip</h4>
                                        <p>Regular maintenance ensures optimal performance and longevity of your plants.</p>
                                    </div>
                                    <div class="tip-card">
                                        <i class="fa fa-calendar"></i>
                                        <h4>Schedule</h4>
                                        <p>Follow a consistent maintenance schedule for best results.</p>
                                    </div>
                                    <div class="tip-card">
                                        <i class="fa fa-shield"></i>
                                        <h4>Safety</h4>
                                        <p>Always use appropriate tools and follow safety guidelines.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                        <div style="display: flex; justify-content: flex-start; padding: 20px 32px; background-color: #2F4F4F; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                            <a href="{{ route('front.maintenance.category', ['categoryId' => $maintenance->product->category_id]) }}" class="btn alazea-btn">
                                ← Back to Products
                            </a>
                        </div>
                    <!-- Action Section -->
                    <div class="action-section text-center">
                        <div class="action-content">
                            <h3>Ready to start your maintenance?</h3>
                            <p>Follow the guide above and keep your plants healthy and thriving.</p>
                            <div class="action-buttons">
                                <a href="{{ route('front.maintenance.category', ['categoryId' => $maintenance->product->category_id]) }}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left mr-2"></i>Back to Category
                                </a>
                                <a href="{{ route('front.maintenance') }}" class="btn btn-primary">
                                    <i class="fa fa-leaf mr-2"></i>More Guides
                                </a>
                                <a href="{{ route('maintenance.pdf', $maintenance->product->id) }}" class="btn btn-dark">
                                    <i class="fa fa-download mr-2"></i>Download PDF
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- ##### Maintenance Details End ##### -->

<style>
/* ===== HERO SECTION ===== */
.hero-area {
    position: relative;
    z-index: 1;
}

.hero-bg {
    position: relative;
    background-attachment: fixed;
    background-size: cover;
    background-position: center;
}

.hero-bg::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0,0,0,0.7) 0%, rgba(112,199,69,0.3) 100%);
    z-index: -1;
}

.hero-title {
    margin: 0;
    line-height: 1.2;
}

.badge-hero {
    background: linear-gradient(135deg, #70c745 0%, #5aa33a 100%);
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    font-size: 1rem;
    font-weight: 500;
    box-shadow: 0 4px 15px rgba(112,199,69,0.3);
}

.btn-hero-favorite {
    background: rgba(255,255,255,0.1);
    border: 2px solid #fff;
    color: #fff;
    padding: 12px 25px;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.btn-hero-favorite:hover,
.btn-hero-favorite.favorited {
    background: #ff6b6b;
    border-color: #ff6b6b;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255,107,107,0.3);
}

.btn-hero-primary {
    background: linear-gradient(135deg, #70c745 0%, #5aa33a 100%);
    border: none;
    color: white;
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-hero-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(112,199,69,0.4);
    color: white;
}

/* ===== MAIN CONTENT AREA ===== */
.maintenance-details-area {
    padding: 80px 0;
    min-height: 100vh;
}

.custom-alert {
    border: none;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

/* ===== MEDIA SECTION ===== */
.maintenance-media-section {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.media-container {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    background: #f8f9fa;
}

.maintenance-video,
.maintenance-image {
    width: 100%;
    height: 400px;
    object-fit: cover;
    display: block;
    transition: transform 0.3s ease;
}

.video-wrapper,
.image-wrapper {
    position: relative;
    cursor: pointer;
}

.video-wrapper:hover .maintenance-video,
.image-wrapper:hover .maintenance-image {
    transform: scale(1.02);
}

.video-overlay,
.image-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(112,199,69,0.9);
    color: white;
    padding: 15px;
    border-radius: 50%;
    opacity: 0;
    transition: all 0.3s ease;
}

.video-wrapper:hover .video-overlay,
.image-wrapper:hover .image-overlay {
    opacity: 1;
}

.video-overlay i,
.image-overlay i {
    font-size: 24px;
}

.placeholder-wrapper {
    position: relative;
    text-align: center;
    padding: 50px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.placeholder-overlay {
    color: #6c757d;
}

.placeholder-overlay i {
    font-size: 48px;
    margin-bottom: 15px;
}

/* ===== QUICK INFO CARD ===== */
.quick-info-card {
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    overflow: hidden;
    height: 100%;
}

.quick-info-card .card-header {
    background: linear-gradient(135deg, #70c745 0%, #5aa33a 100%);
    color: white;
    padding: 20px;
    margin: 0;
    border: none;
}

.quick-info-card .card-header h4 {
    margin: 0;
    font-weight: 600;
}

.quick-info-card .card-body {
    padding: 25px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e9ecef;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #495057;
}

.info-value {
    font-weight: 500;
    color: #212529;
}

.badge-category {
    background: linear-gradient(135deg, #70c745 0%, #5aa33a 100%);
    color: white;
    padding: 6px 12px;
    border-radius: 15px;
}

/* ===== CONTENT SECTIONS ===== */
.maintenance-content {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin-top: 30px;
}

.content-section {
    margin-bottom: 50px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 3px solid #70c745;
}

.section-header h2,
.section-header h3 {
    margin: 0;
    color: #2c3e50;
    font-weight: 700;
}

.section-header h2 {
    font-size: 2rem;
}

.section-header h3 {
    font-size: 1.5rem;
}

.badge-required {
    background: #dc3545;
    color: white;
}

.badge-optional {
    background: #6c757d;
    color: white;
}

.badge-steps {
    background: #70c745;
    color: white;
}

.description-box {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 30px;
    border-radius: 15px;
    border-left: 5px solid #70c745;
}

.description-box p {
    margin: 0;
    font-size: 1.1rem;
    line-height: 1.8;
    color: #495057;
}

/* ===== MATERIALS SECTION ===== */
.materials-section {
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    height: 100%;
}

.materials-content {
    margin-top: 20px;
}

.material-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    text-align: center;
}

.material-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.material-card.featured {
    border: 2px solid #70c745;
}

.material-image {
    width: 80px;
    height: 80px;
    margin: 0 auto 15px;
    border-radius: 50%;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.material-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.material-info h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-weight: 600;
}

.material-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #70c745;
    margin: 8px 0;
}

.material-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.material-badge {
    background: #70c745;
    color: white;
}

.material-badge.optional {
    background: #6c757d;
    color: white;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state p {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.empty-state small {
    font-size: 0.9rem;
    opacity: 0.8;
}

/* ===== STEPS SECTION ===== */
.steps-container {
    position: relative;
}

.step-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 30px;
    position: relative;
}

.step-number {
    flex-shrink: 0;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #70c745 0%, #5aa33a 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    margin-right: 20px;
    box-shadow: 0 5px 15px rgba(112,199,69,0.3);
    z-index: 2;
    position: relative;
}

.step-content {
    flex: 1;
    background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    position: relative;
    top: -5px;
}

.step-title {
    margin: 0 0 12px 0;
    color: #2c3e50;
    font-weight: 600;
    font-size: 1.3rem;
}

.step-description {
    margin: 0;
    color: #495057;
    line-height: 1.7;
    font-size: 1rem;
}

.step-connector {
    position: absolute;
    left: 24px;
    top: 50px;
    width: 2px;
    height: 40px;
    background: linear-gradient(to bottom, #70c745, #5aa33a);
    z-index: 1;
}

/* ===== GENERAL MAINTENANCE ===== */
.general-maintenance {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 20px;
    padding: 40px;
}

.maintenance-tips {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.tip-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.tip-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.tip-card i {
    font-size: 2.5rem;
    color: #70c745;
    margin-bottom: 15px;
}

.tip-card h4 {
    margin: 0 0 12px 0;
    color: #2c3e50;
    font-weight: 600;
}

.tip-card p {
    margin: 0;
    color: #495057;
    line-height: 1.6;
}

/* ===== ACTION SECTION ===== */
.action-section {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: white;
    padding: 50px 30px;
    border-radius: 20px;
    margin-top: 50px;
}

.action-content h3 {
    margin-bottom: 15px;
    font-size: 2rem;
    font-weight: 700;
}

.action-content p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin-bottom: 30px;
}

.action-buttons .btn {
    margin: 0 10px;
    padding: 12px 30px;
    border-radius: 50px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.action-buttons .btn-secondary {
    background: rgba(255,255,255,0.1);
    border: 2px solid rgba(255,255,255,0.3);
    color: white;
}

.action-buttons .btn-secondary:hover {
    background: rgba(255,255,255,0.2);
    transform: translateY(-2px);
    color: white;
}

.action-buttons .btn-primary {
    background: linear-gradient(135deg, #70c745 0%, #5aa33a 100%);
    border: none;
}

.action-buttons .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(112,199,69,0.4);
}

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem !important;
    }
    
    .hero-actions {
        flex-direction: column;
        gap: 15px;
    }
    
    .hero-actions .btn {
        width: 100%;
        margin: 0;
    }
    
    .maintenance-media-section,
    .maintenance-content {
        padding: 20px;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .step-item {
        flex-direction: column;
        text-align: center;
    }
    
    .step-number {
        margin: 0 auto 20px;
    }
    
    .step-connector {
        display: none;
    }
    
    .maintenance-tips {
        grid-template-columns: 1fr;
    }
    
    .action-buttons .btn {
        display: block;
        width: 100%;
        margin: 10px 0;
    }
}

@media (max-width: 576px) {
    .maintenance-details-area {
        padding: 40px 0;
    }
    
    .hero-title {
        font-size: 1.8rem !important;
    }
    
    .section-header h2 {
        font-size: 1.5rem;
    }
    
    .section-header h3 {
        font-size: 1.3rem;
    }
}

/* ===== SMOOTH SCROLLING ===== */
html {
    scroll-behavior: smooth;
}

/* ===== FAVORITE BUTTON UPDATES ===== */
#favoriteBtn.favorited {
    background: #ff6b6b !important;
    border-color: #ff6b6b !important;
}

#favoriteBtn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<script>
function toggleFavorite(productId) {
    const btn = document.getElementById('favoriteBtn');
    const icon = document.getElementById('favoriteIcon');
    const text = document.getElementById('favoriteText');
    
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
            if (data.is_favorited) {
                icon.className = 'fa fa-heart';
                text.textContent = 'Favorited';
                btn.classList.add('favorited');
            } else {
                icon.className = 'fa fa-heart-o';
                text.textContent = 'Add to Favori';
                btn.classList.remove('favorited');
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

// ===== CHAT BOT FUNCTIONALITY =====
const chatWidget = {
    isOpen: false,
    messages: [],
    
    init() {
        this.createWidget();
        this.attachEventListeners();
        this.loadSuggestions();
    },
    
    createWidget() {
        const widget = document.createElement('div');
        widget.id = 'plantChatWidget';
        widget.innerHTML = `
            <!-- Chat Toggle Button -->
            <button id="chatToggleBtn" class="chat-toggle-btn" onclick="chatWidget.toggle()">
                <i class="fa fa-comments"></i>
                <span class="chat-badge">AI</span>
            </button>
            
            <!-- Chat Container -->
            <div id="chatContainer" class="chat-container" style="display: none;">
                <div class="chat-header">
                    <div class="chat-header-content">
                        <div class="chat-avatar">
                            <i class="fa fa-leaf"></i>
                        </div>
                        <div class="chat-title">
                            <h4>🌿 Assistant UrbanGreen</h4>
                            <p class="chat-status">
                                <span class="status-dot"></span>
                                En ligne
                            </p>
                        </div>
                    </div>
                    <button class="chat-close-btn" onclick="chatWidget.toggle()">
                        <i class="fa fa-times"></i>
                    </button>
                </div>
                
                <div class="chat-suggestions" id="chatSuggestions">
                    <div class="suggestions-header">💡 Questions suggérées</div>
                    <div id="suggestionsContent" class="suggestions-content">
                        <div class="loading-suggestions">Chargement...</div>
                    </div>
                </div>
                
                <div class="chat-messages" id="chatMessages">
                    <div class="welcome-message">
                        <div class="bot-avatar">🌱</div>
                        <div class="message-bubble bot">
                            <p>👋 Bonjour! Je suis votre assistant virtuel pour l'entretien de <strong>${this.getProductName()}</strong>.</p>
                            <p>Posez-moi vos questions sur l'arrosage, la lumière, la fertilisation, etc.</p>
                        </div>
                    </div>
                </div>
                
                <div class="chat-input-area">
                    <div class="input-wrapper">
                        <input 
                            type="text" 
                            id="chatInput" 
                            placeholder="Posez votre question..."
                            autocomplete="off"
                        />
                        <button id="chatSendBtn" onclick="chatWidget.sendMessage()">
                            <i class="fa fa-paper-plane"></i>
                        </button>
                    </div>
                    <div class="powered-by">
                        Propulsé par <strong>Gemini AI</strong>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(widget);
    },
    
    getProductName() {
        return "{{ $maintenance->product->name ?? 'vos plantes' }}";
    },
    
    toggle() {
        this.isOpen = !this.isOpen;
        const container = document.getElementById('chatContainer');
        const toggleBtn = document.getElementById('chatToggleBtn');
        
        if (this.isOpen) {
            container.style.display = 'flex';
            toggleBtn.style.display = 'none';
            document.getElementById('chatInput').focus();
        } else {
            container.style.display = 'none';
            toggleBtn.style.display = 'flex';
        }
    },
    
    attachEventListeners() {
        const input = document.getElementById('chatInput');
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });
    },
    
    async loadSuggestions() {
        try {
            const response = await fetch('/api/chat/suggestions');
            const data = await response.json();
            
            if (data.success) {
                this.renderSuggestions(data.suggestions);
            }
        } catch (error) {
            console.error('Failed to load suggestions:', error);
            document.getElementById('suggestionsContent').innerHTML = 
                '<p class="text-muted">Impossible de charger les suggestions</p>';
        }
    },
    
    renderSuggestions(suggestions) {
        const container = document.getElementById('suggestionsContent');
        container.innerHTML = suggestions.slice(0, 2).map(category => `
            <div class="suggestion-category">
                <div class="category-name">${category.icon} ${category.category}</div>
                ${category.questions.slice(0, 2).map(q => `
                    <button class="suggestion-chip" onclick="chatWidget.askQuestion(\`${q.replace(/`/g, '\\`')}\`)">
                        ${q}
                    </button>
                `).join('')}
            </div>
        `).join('');
    },
    
    askQuestion(question) {
        document.getElementById('chatInput').value = question;
        this.sendMessage();
    },
    
    async sendMessage() {
        const input = document.getElementById('chatInput');
        const question = input.value.trim();
        
        if (!question) return;
        
        // Add user message
        this.addMessage(question, true);
        input.value = '';
        
        // Hide suggestions
        document.getElementById('chatSuggestions').style.display = 'none';
        
        // Show loading
        this.showLoading();
        
        try {
            const response = await fetch('/api/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ question })
            });
            
            const data = await response.json();
            this.hideLoading();
            
            if (data.success) {
                this.addMessage(data.data.answer, false, data.data);
            } else {
                this.addMessage('❌ ' + (data.message || 'Une erreur s\'est produite'), false);
            }
        } catch (error) {
            this.hideLoading();
            this.addMessage('❌ Impossible de contacter le service. Le serveur Python est-il démarré ?', false);
            console.error('Chat error:', error);
        }
    },
    
    addMessage(text, isUser = false, metadata = {}) {
        const messagesContainer = document.getElementById('chatMessages');
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${isUser ? 'user' : 'bot'}`;
        
        let metaHtml = '';
        if (!isUser && metadata.confidence !== undefined) {
            const confidencePercent = Math.round(metadata.confidence * 100);
            metaHtml = `<div class="message-meta">
                Confiance: ${confidencePercent}%
                ${metadata.plant ? ` | Plante: ${metadata.plant}` : ''}
            </div>`;
        }
        
        messageDiv.innerHTML = `
            <div class="${isUser ? 'user-avatar' : 'bot-avatar'}">${isUser ? '👤' : '🌱'}</div>
            <div class="message-bubble ${isUser ? 'user' : 'bot'}">
                ${text.replace(/\n/g, '<br>')}
                ${metaHtml}
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    },
    
    showLoading() {
        const messagesContainer = document.getElementById('chatMessages');
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'chat-message bot';
        loadingDiv.id = 'chatLoading';
        loadingDiv.innerHTML = `
            <div class="bot-avatar">🌱</div>
            <div class="message-bubble bot loading">
                <div class="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            </div>
        `;
        messagesContainer.appendChild(loadingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    },
    
    hideLoading() {
        const loading = document.getElementById('chatLoading');
        if (loading) loading.remove();
    }
};

// Initialize chat widget when page loads
document.addEventListener('DOMContentLoaded', function() {
    chatWidget.init();
});
</script>

<style>
/* ===== CHAT WIDGET STYLES ===== */
#plantChatWidget {
    position: fixed;
    bottom: 30px;
    left: 30px;
    z-index: 1000;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.chat-toggle-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #70c745 0%, #5aa33a 100%);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 8px 25px rgba(112, 199, 69, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    transition: all 0.3s ease;
    animation: pulse 2s infinite;
}

.chat-toggle-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 12px 35px rgba(112, 199, 69, 0.6);
}

.chat-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #ff6b6b;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 3px 6px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(255, 107, 107, 0.4);
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 8px 25px rgba(112, 199, 69, 0.4);
    }
    50% {
        box-shadow: 0 8px 35px rgba(112, 199, 69, 0.7);
    }
}

.chat-container {
    width: 380px;
    height: 600px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chat-header {
    background: linear-gradient(135deg, #70c745 0%, #5aa33a 100%);
    color: white;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-avatar {
    width: 45px;
    height: 45px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
}

.chat-title h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.chat-status {
    margin: 2px 0 0 0;
    font-size: 12px;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 5px;
}

.status-dot {
    width: 8px;
    height: 8px;
    background: #4ade80;
    border-radius: 50%;
    display: inline-block;
    animation: blink 2s infinite;
}

@keyframes blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

.chat-close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 18px;
    transition: all 0.3s ease;
}

.chat-close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.chat-suggestions {
    background: #f8f9fa;
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
    max-height: 150px;
    overflow-y: auto;
}

.suggestions-header {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
    margin-bottom: 10px;
}

.suggestions-content {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.suggestion-category {
    margin-bottom: 5px;
}

.category-name {
    font-size: 12px;
    font-weight: 600;
    color: #70c745;
    margin-bottom: 5px;
}

.suggestion-chip {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 15px;
    padding: 6px 12px;
    font-size: 12px;
    cursor: pointer;
    margin: 3px;
    transition: all 0.3s ease;
    display: inline-block;
}

.suggestion-chip:hover {
    background: #70c745;
    color: white;
    border-color: #70c745;
    transform: translateY(-2px);
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #fafafa;
}

.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: #70c745;
    border-radius: 3px;
}

.welcome-message,
.chat-message {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 15px;
}

.chat-message.user {
    flex-direction: row-reverse;
}

.bot-avatar,
.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}

.bot-avatar {
    background: linear-gradient(135deg, #70c745 0%, #5aa33a 100%);
}

.user-avatar {
    background: #e0e0e0;
}

.message-bubble {
    max-width: 75%;
    padding: 12px 16px;
    border-radius: 15px;
    line-height: 1.5;
    font-size: 14px;
}

.message-bubble.bot {
    background: white;
    border: 1px solid #e9ecef;
    border-bottom-left-radius: 4px;
}

.message-bubble.user {
    background: linear-gradient(135deg, #70c745 0%, #5aa33a 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.message-bubble p {
    margin: 0 0 8px 0;
}

.message-bubble p:last-child {
    margin-bottom: 0;
}

.message-meta {
    font-size: 11px;
    margin-top: 8px;
    opacity: 0.7;
    padding-top: 8px;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.message-bubble.loading {
    padding: 16px;
}

.typing-indicator {
    display: flex;
    gap: 4px;
}

.typing-indicator span {
    width: 8px;
    height: 8px;
    background: #70c745;
    border-radius: 50%;
    animation: typing 1.4s infinite;
}

.typing-indicator span:nth-child(2) {
    animation-delay: 0.2s;
}

.typing-indicator span:nth-child(3) {
    animation-delay: 0.4s;
}

@keyframes typing {
    0%, 60%, 100% {
        transform: translateY(0);
        opacity: 0.7;
    }
    30% {
        transform: translateY(-10px);
        opacity: 1;
    }
}

.chat-input-area {
    padding: 15px;
    background: white;
    border-top: 1px solid #e9ecef;
}

.input-wrapper {
    display: flex;
    gap: 10px;
    align-items: center;
    background: #f8f9fa;
    border-radius: 25px;
    padding: 8px 12px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.input-wrapper:focus-within {
    border-color: #70c745;
    background: white;
}

#chatInput {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 14px;
    padding: 5px;
}

#chatSendBtn {
    background: linear-gradient(135deg, #70c745 0%, #5aa33a 100%);
    border: none;
    color: white;
    width: 35px;
    height: 35px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

#chatSendBtn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(112, 199, 69, 0.4);
}

.powered-by {
    text-align: center;
    font-size: 11px;
    color: #6c757d;
    margin-top: 8px;
}

.loading-suggestions {
    text-align: center;
    color: #6c757d;
    font-size: 13px;
    padding: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    #plantChatWidget {
        bottom: 20px;
        left: 20px;
    }
    
    .chat-container {
        width: calc(100vw - 40px);
        height: calc(100vh - 100px);
        max-width: 380px;
    }
}
</style>

@endsection