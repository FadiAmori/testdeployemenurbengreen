{{-- resources/views/urbangreen/dataset-event-show.blade.php --}}
@extends('urbangreen.layouts.main')

@section('content')
<section class="section-padding-100">
    <div class="container">
        <!-- Demo Notice -->
        <div class="alert alert-info alert-dismissible fade show rounded-4 mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-info-circle me-3 fs-4"></i>
                <div>
                    <strong>Demo Event from ML Recommendation System</strong>
                    <p class="mb-0 small">This event is from our machine learning dataset for demonstration purposes. It showcases the recommendation capabilities with 10,000 synthetic urban gardening events.</p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>

        <!-- Hero Section -->
        <div class="hero-event position-relative overflow-hidden rounded-4 mb-5 shadow-lg" style="height: 500px; background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://via.placeholder.com/800x500/28a745/ffffff?text={{ urlencode($event['title'] ?? 'Dataset Event') }}'); background-size: cover; background-position: center;">
            <div class="hero-overlay d-flex align-items-end h-100 p-5 text-white">
                <div class="col-lg-8">
                    <h1 class="display-3 fw-bold mb-3 animate-fade-in">{{ $event['title'] ?? 'Event Title' }}</h1>
                    <div class="d-flex flex-wrap gap-3 mb-4 animate-fade-in-delay">
                        @if(isset($event['event_date']))
                            <div class="d-flex align-items-center">
                                <i class="fa fa-calendar-alt me-2"></i>
                                <span class="lead">{{ \Carbon\Carbon::parse($event['event_date'])->format('F j, Y') }}</span>
                            </div>
                        @endif
                        @if(isset($event['location']))
                            <div class="d-flex align-items-center">
                                <i class="fa fa-map-marker-alt me-2"></i>
                                <span class="lead">{{ $event['location'] }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        @if(isset($event['category']))
                            <span class="badge bg-success fs-6 px-4 py-2 rounded-pill animate-fade-in-delay2 shadow-sm">
                                <i class="fa fa-tag me-1"></i>{{ $event['category'] }}
                            </span>
                        @endif
                        @if(isset($event['plant_step']))
                            <span class="badge bg-info fs-6 px-4 py-2 rounded-pill animate-fade-in-delay2 shadow-sm">
                                <i class="fa fa-seedling me-1"></i>{{ $event['plant_step'] }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-5">
            <div class="col-lg-8">
                <!-- Event Details Card -->
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-5">
                        <h3 class="fw-bold text-green mb-4"><i class="fa fa-info-circle me-2"></i>Event Details</h3>
                        
                        @if(isset($event['description']))
                            <div class="lead text-muted fs-5 lh-lg mb-4">
                                {{ $event['description'] }}
                            </div>
                        @endif

                        <!-- Additional Info Grid -->
                        <div class="row g-4 mt-4">
                            @if(isset($event['duration_hours']))
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <i class="fa fa-clock text-success fs-3 me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Duration</small>
                                            <strong>{{ $event['duration_hours'] }} hours</strong>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($event['capacity']))
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <i class="fa fa-users text-success fs-3 me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Capacity</small>
                                            <strong>{{ $event['capacity'] }} participants</strong>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($event['difficulty_level']))
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <i class="fa fa-chart-line text-success fs-3 me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Difficulty</small>
                                            <strong>{{ ucfirst($event['difficulty_level']) }}</strong>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($event['tools_needed']))
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <i class="fa fa-tools text-success fs-3 me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Tools Needed</small>
                                            <strong>{{ $event['tools_needed'] ? 'Yes' : 'No' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($event['weather_dependent']))
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <i class="fa fa-cloud-sun text-success fs-3 me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Weather Dependent</small>
                                            <strong>{{ $event['weather_dependent'] ? 'Yes' : 'No' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if(isset($event['cost']))
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center p-3 bg-light rounded-3">
                                        <i class="fa fa-dollar-sign text-success fs-3 me-3"></i>
                                        <div>
                                            <small class="text-muted d-block">Cost</small>
                                            <strong>{{ $event['cost'] > 0 ? '$' . number_format($event['cost'], 2) : 'Free' }}</strong>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Dataset Info -->
                        <div class="mt-5 p-4 bg-light rounded-4 border-start border-4 border-info">
                            <h6 class="fw-bold text-info mb-2"><i class="fa fa-database me-2"></i>Dataset Information</h6>
                            <p class="text-muted mb-1 small"><strong>Event ID:</strong> {{ $event['event_id'] ?? 'N/A' }}</p>
                            <p class="text-muted mb-0 small">This is a synthetic event generated for machine learning training and demonstration of our recommendation system. The ML model was trained on 49,963 user-event interactions to provide personalized event suggestions.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Info Card -->
                <div class="card border-0 shadow-lg rounded-4 mb-4 sticky-top" style="top: 100px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4 text-green"><i class="fa fa-lightbulb me-2"></i>About This Event</h5>
                        
                        <ul class="list-unstyled mb-4">
                            @if(isset($event['event_date']))
                                <li class="mb-3 d-flex align-items-start">
                                    <i class="fa fa-calendar text-success me-3 mt-1"></i>
                                    <div>
                                        <small class="text-muted d-block">Date</small>
                                        <strong>{{ \Carbon\Carbon::parse($event['event_date'])->format('l, F j, Y') }}</strong>
                                    </div>
                                </li>
                            @endif
                            @if(isset($event['location']))
                                <li class="mb-3 d-flex align-items-start">
                                    <i class="fa fa-map-marker-alt text-success me-3 mt-1"></i>
                                    <div>
                                        <small class="text-muted d-block">Location</small>
                                        <strong>{{ $event['location'] }}</strong>
                                    </div>
                                </li>
                            @endif
                            @if(isset($event['plant_step']))
                                <li class="mb-3 d-flex align-items-start">
                                    <i class="fa fa-leaf text-success me-3 mt-1"></i>
                                    <div>
                                        <small class="text-muted d-block">Plant Step</small>
                                        <strong>{{ $event['plant_step'] }}</strong>
                                    </div>
                                </li>
                            @endif
                        </ul>

                        <div class="alert alert-warning mb-0 rounded-3">
                            <i class="fa fa-exclamation-triangle me-2"></i>
                            <small><strong>Demo Mode:</strong> This is a demonstration event. Registration is not available for dataset events.</small>
                        </div>
                    </div>
                </div>

                <!-- ML Recommendation Info -->
                <div class="card border-0 shadow-sm rounded-4 bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body p-4 text-white">
                        <h6 class="fw-bold mb-3"><i class="fa fa-brain me-2"></i>Powered by AI</h6>
                        <p class="small mb-0">This event was recommended by our machine learning system based on user preferences and behavior patterns from 10,000 urban gardening events.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.animate-fade-in {
    animation: fadeIn 0.8s ease-in;
}
.animate-fade-in-delay {
    animation: fadeIn 1s ease-in;
}
.animate-fade-in-delay2 {
    animation: fadeIn 1.2s ease-in;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.text-green {
    color: #28a745 !important;
}
.border-success {
    border-color: #28a745 !important;
}
</style>
@endsection
