{{-- resources/views/urbangreen/event-show.blade.php --}}
@extends('urbangreen.layouts.main')

@section('content')
<section class="section-padding-100">
    <div class="container">
        @if(session('status'))
            <div class="alert alert-success alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 z-3 rounded-4 shadow" style="width: 90%; max-width: 500px;" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa fa-check-circle me-2"></i>
                    <div>{{ session('status') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3 z-3 rounded-4 shadow" style="width: 90%; max-width: 500px;" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa fa-exclamation-triangle me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Hero Section -->
        <div class="hero-event position-relative overflow-hidden rounded-4 mb-5 shadow-lg" style="height: 500px; background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ $event->image && Storage::exists($event->image) ? Storage::url($event->image) : 'https://via.placeholder.com/800x500/28a745/ffffff?text=Event+Image' }}'); background-size: cover; background-position: center;">
            <div class="hero-overlay d-flex align-items-end h-100 p-5 text-white">
                <div class="col-lg-8">
                    <h1 class="display-3 fw-bold mb-3 animate-fade-in">{{ $event->title }}</h1>
                    <div class="d-flex flex-wrap gap-3 mb-4 animate-fade-in-delay">
                        <div class="d-flex align-items-center">
                            <i class="fa fa-calendar-alt me-2"></i>
                            <span class="lead">{{ $event->event_date->format('F j, Y, g:i A') }}</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fa fa-map-marker-alt me-2"></i>
                            <span class="lead">{{ $event->location }}</span>
                        </div>
                    </div>
                    @if($event->category)
                        <span class="badge bg-success fs-6 px-4 py-2 rounded-pill animate-fade-in-delay2 shadow-sm">{{ $event->category }}</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row g-5">
            <div class="col-lg-8">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs nav-pills nav-fill mb-4 rounded-pill bg-white shadow-sm" id="eventTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill w-100 px-4 py-3 fs-6 fw-semibold" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                            <i class="fa fa-info-circle me-2"></i>Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill w-100 px-4 py-3 fs-6 fw-semibold" id="participants-tab" data-bs-toggle="pill" data-bs-target="#participants" type="button" role="tab">
                            <i class="fa fa-users me-2"></i>Participants
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content" id="eventTabsContent">
                    <!-- Overview Tab -->
                    <div class="tab-pane fade show active" id="overview" role="tabpanel">
                        <div class="card border-0 shadow-lg rounded-4">
                            <div class="card-body p-5">
                                <h3 class="fw-bold text-green mb-4"><i class="fa fa-info-circle me-2"></i>Event Details</h3>
                                <div class="lead text-muted fs-5 lh-lg">{{ $event->description }}</div>
                                
                                <!-- Organizer -->
                                @if($event->user)
                                    <div class="d-flex align-items-center mt-5 p-4 bg-light rounded-4 border-start border-4 border-success shadow-sm">
                                        <img src="{{ $event->user->profile_photo && Storage::exists($event->user->profile_photo) ? Storage::url($event->user->profile_photo) : 'https://via.placeholder.com/60x60/28a745/ffffff?text=User' }}" class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;" alt="Organizer">
                                        <div>
                                            <h6 class="fw-bold mb-1 text-dark">Organized by {{ $event->user->name }}</h6>
                                            <p class="text-muted mb-0">Event Creator & Coordinator</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Map Section -->
                        <div class="card border-0 shadow-lg rounded-4 mt-4">
                            <div class="card-body p-5">
                                <h5 class="fw-bold mb-4 text-green d-flex align-items-center"><i class="fa fa-map-marker-alt me-2"></i>Event Location</h5>
                                <div id="map" style="height: 400px; width: 100%; border-radius: 1rem; background: #f8f9fa; border: 2px solid #e9ecef;"></div>
                                <div id="map-message" class="mt-3"></div>
                                @if (session('map_error'))
                                    <div class="alert alert-warning rounded-3 mt-3">{{ session('map_error') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Participants Tab -->
                    <div class="tab-pane fade" id="participants" role="tabpanel">
                        <div class="card border-0 shadow-lg rounded-4">
                            <div class="card-body p-5">
                                <h3 class="fw-bold text-green mb-4 d-flex align-items-center"><i class="fa fa-users me-2"></i>Participants ({{ $event->users_count }})</h3>
                                @auth
                                    @if($event->users->contains(auth()->id()))
                                        <div class="alert alert-info rounded-4 mb-4 p-4 border-start border-4 border-info shadow-sm">
                                            <div class="d-flex align-items-center mb-3">
                                                <i class="fa fa-user-check me-2 text-info fs-4"></i>
                                                {!! $event->statusBadge(auth()->id()) !!}
                                            </div>
                                            @if($event->getAttendanceStatusForEvent(auth()->id()) === 'pending' && !$event->event_date->isPast())
                                                <form action="{{ route('front.event.confirm', $event) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success rounded-pill px-4 py-2">Confirm Attendance</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                @endauth

                                @if($event->users->count() > 0)
                                    <div class="row g-4">
                                        @foreach($event->users as $participant)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="d-flex align-items-center p-4 bg-light rounded-4 shadow-sm border border-light">
                                                    <img src="{{ $participant->profile_photo && Storage::exists($participant->profile_photo) ? Storage::url($participant->profile_photo) : 'https://via.placeholder.com/50x50/28a745/ffffff?text=U' }}" class="rounded-circle me-3 shadow" style="width: 50px; height: 50px; object-fit: cover;" alt="{{ $participant->name }}">
                                                    <div class="flex-grow-1">
                                                        <h6 class="fw-bold mb-1 text-dark">{{ $participant->name }}</h6>
                                                        <div class="d-flex align-items-center">
                                                            <small class="text-muted me-2">Status:</small>
                                                            <span class="badge {{ $participant->pivot->attendance_status === 'pending' ? 'bg-warning text-dark' : ($participant->pivot->attendance_status === 'confirmed' ? 'bg-info' : ($participant->pivot->attendance_status === 'attended' ? 'bg-success' : 'bg-secondary')) }} fs-6 px-3 py-2 fw-semibold">{{ ucfirst($participant->pivot->attendance_status) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-6 bg-light rounded-4">
                                        <i class="fa fa-users fa-4x text-muted mb-3"></i>
                                        <h5 class="text-muted">No participants yet</h5>
                                        <p class="text-muted">Be the first to join this event and make a difference!</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Join Action Card -->
                <div class="card border-0 shadow-lg rounded-4 sticky-top" style="top: 20px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4 text-green text-center"><i class="fa fa-ticket-alt me-2"></i>Ready to Join?</h5>
                        @auth
                            @if($event->users->contains(auth()->id()))
                                <div class="text-center mb-4">
                                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px; font-size: 2rem;">
                                        <i class="fa fa-check"></i>
                                    </div>
                                    <button class="btn btn-success w-100 rounded-pill mb-3 disabled fs-6 fw-semibold" style="height: 50px;">
                                        You're Joined!
                                    </button>
                                </div>
                            @else
                                <form action="{{ route('front.event.enroll', $event) }}" method="POST" class="text-center mb-4">
                                    @csrf
                                    <button type="submit" class="btn btn-success w-100 rounded-pill shadow-sm" style="height: 50px; font-size: 1.1rem;">
                                        <i class="fa fa-plus me-2"></i>Join This Event
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-success w-100 rounded-pill shadow-sm mb-4" style="height: 50px; font-size: 1.1rem;">
                                <i class="fa fa-sign-in-alt me-2"></i>Login to Join
                            </a>
                        @endauth
                        <hr class="my-4">
                        <ul class="list-unstyled text-center">
                            <li class="mb-2"><i class="fa fa-calendar-alt me-2 text-green"></i><small class="text-muted">Date: {{ $event->event_date->format('M j, Y') }}</small></li>
                            <li class="mb-2"><i class="fa fa-map-marker-alt me-2 text-green"></i><small class="text-muted">Location: {{ $event->location }}</small></li>
                            @if($event->max_participants)
                                <li><i class="fa fa-users me-2 text-green"></i><small class="text-muted">Spots: {{ $event->users_count }} / {{ $event->max_participants }}</small></li>
                            @endif
                        </ul>

                        <!-- Enhanced QR Code Section -->
                        <div class="mt-5 text-center">
                            <h6 class="fw-bold text-green mb-3"><i class="fa fa-qrcode me-2"></i>Quick Access</h6>
                            <div class="qr-container position-relative p-4 rounded-4 shadow-lg border border-light mx-auto" style="width: 200px; height: 200px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
                                {!! QrCode::size(160)->errorCorrection('H')->generate(route('front.event.show', $event)) !!}
                                <div class="position-absolute bottom-0 start-50 translate-middle-x pb-2">
                                    <small class="text-muted bg-white px-2 rounded">Scan Me</small>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-2">Scan for instant event details on mobile</small>
                            <div class="mt-3 d-flex gap-2">
                                <button class="btn btn-outline-success btn-sm rounded-pill flex-grow-1" onclick="navigator.share({title: '{{ $event->title }}', url: '{{ route('front.event.show', $event) }}'}).catch(() => { navigator.clipboard.writeText('{{ route('front.event.show', $event) }}'); alert('Link copied to clipboard!'); });">
                                    <i class="fa fa-share-alt me-1"></i>Share
                                </button>
                                <a href="data:image/svg+xml;base64,{!! base64_encode(QrCode::format('svg')->size(200)->errorCorrection('H')->generate(route('front.event.show', $event))) !!}" download="event-{{ $event->slug ?? $event->id }}.svg" class="btn btn-success btn-sm rounded-pill">
                                    <i class="fa fa-download me-1"></i>Download QR
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI-Powered Recommendations -->
                <div class="card border-0 shadow-lg rounded-4 mt-4" id="ai-recommendations">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="fw-bold text-green mb-1 d-flex align-items-center"><i class="fa fa-brain me-2"></i>Recommended Events</h5>
                        <small class="text-muted">Based on your interests</small>
                    </div>
                    <div class="card-body pt-3" id="ai-rec-list">
                        <div class="text-center py-4">
                            <div class="spinner-border text-success spinner-border-sm mb-2" role="status"></div>
                            <p class="text-muted mb-0">Loading personalized recommendations...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="{{ route('front.event') }}" class="btn btn-outline-secondary rounded-pill px-5 py-3 fs-5 shadow-sm">
                <i class="fa fa-arrow-left me-2"></i>Back to All Events
            </a>
        </div>
    </div>
</section>

<!-- Professional Custom CSS -->
<style>
    :root {
        --green-primary: #28a745;
        --green-light: #d4edda;
        --shadow-lg: 0 10px 30px rgba(0,0,0,0.1);
        --shadow-xl: 0 20px 40px rgba(0,0,0,0.15);
    }
    .text-green { color: var(--green-primary) !important; }
    .hero-event { border-radius: 1.5rem; overflow: hidden; box-shadow: var(--shadow-xl); }
    .hero-overlay { background: rgba(0,0,0,0.6); }
    .animate-fade-in { animation: fadeIn 1s ease forwards; }
    .animate-fade-in-delay { animation: fadeIn 1s ease 0.3s forwards; opacity: 0; }
    .animate-fade-in-delay2 { animation: fadeIn 1s ease 0.6s forwards; opacity: 0; }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .nav-pills .nav-link { transition: all 0.3s ease; border: 1px solid #dee2e6; }
    .nav-pills .nav-link.active { 
        background: linear-gradient(135deg, var(--green-primary), #20c997); 
        color: white; 
        border-color: var(--green-primary);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }
    .nav-pills .nav-link:hover:not(.active) { color: var(--green-primary); border-color: var(--green-primary); }
    .card { transition: all 0.3s ease; border: none; }
    .card:hover { box-shadow: var(--shadow-xl) !important; transform: translateY(-2px); }
    .ai-rec-item {
        transition: all 0.3s ease;
        cursor: pointer;
        border-radius: 0.75rem;
    }
    .ai-rec-item:hover { 
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(40, 167, 69, 0.1));
        transform: translateX(5px); 
    }
    .predicted-score { font-size: 0.85rem; color: var(--green-primary); font-weight: bold; }
    .qr-container { transition: all 0.3s ease; }
    .qr-container:hover { transform: scale(1.02); box-shadow: var(--shadow-lg); }
    .badge { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .btn { transition: all 0.3s ease; font-weight: 500; }
    .btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
    @media (max-width: 768px) {
        .hero-event { height: 400px; }
        .hero-overlay { padding: 2rem; }
        .hero-event h1 { font-size: 2.5rem; }
        .card-body { padding: 2rem !important; }
        .nav-pills .nav-link { padding: 1rem 0.5rem; font-size: 0.9rem; }
        #map { height: 300px; }
    }
    @media (max-width: 576px) {
        .hero-event { height: 350px; }
        .hero-overlay { padding: 1.5rem; flex-direction: column; text-align: center; }
        .hero-event h1 { font-size: 2rem; }
        .d-flex.flex-wrap.gap-3 { justify-content: center; }
        .btn { font-size: 0.9rem; padding: 0.75rem; }
    }
</style>

<!-- Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<!-- JS for Map and AI Recommendations -->
<script>
    window.Laravel = { baseURL: '{{ url("") }}' };

    // Leaflet Map Initialization
    document.addEventListener('DOMContentLoaded', function() {
        const location = "{{ $event->location }}".trim();
        const mapDiv = document.getElementById('map');
        const mapMessage = document.getElementById('map-message');

        if (!location) {
            if (mapMessage) mapMessage.innerHTML = '<div class="alert alert-info rounded-3"><i class="fa fa-info-circle me-2"></i><p class="mb-0">No location provided for this event.</p></div>';
            return;
        }

        if (typeof L === 'undefined') {
            console.error('Leaflet not loaded.');
            const msg = 'Map unavailable. <a href="https://www.openstreetmap.org/search?query=' + encodeURIComponent(location) + '" target="_blank" class="text-decoration-none">View on OpenStreetMap</a>';
            if (mapMessage) mapMessage.innerHTML = '<div class="alert alert-warning rounded-3"><p class="mb-0">' + msg + '</p></div>';
            return;
        }

        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(location)}&limit=5&addressdetails=1`, {
            headers: { 'User-Agent': 'UrbanGreenEventApp/1.0 (contact: your-email@example.com)' }
        })
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    let result = data.find(item => item.type === 'city' && item.name.toLowerCase().includes(location.split(' ')[0].toLowerCase()));
                    if (!result) result = data.find(item => item.addresstype !== 'country');
                    if (!result) result = data[0];

                    const lat = parseFloat(result.lat), lon = parseFloat(result.lon);
                    const displayName = result.display_name;

                    const map = L.map('map').setView([lat, lon], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(map);

                    const marker = L.marker([lat, lon]).addTo(map);
                    marker.bindPopup(`
                        <div class="p-3 text-start">
                            <h6 class="fw-bold mb-2 text-success">{{ $event->title }}</h6>
                            <p class="mb-2"><i class="fa fa-calendar-alt me-1"></i>{{ $event->event_date->format('M j, Y g:i A') }}</p>
                            <p class="mb-0 small text-muted">${displayName}</p>
                        </div>
                    `).openPopup();

                    if (mapMessage) mapMessage.innerHTML = '';
                } else {
                    const msg = `Location "${location}" not found. <a href="https://www.openstreetmap.org/search?query=${encodeURIComponent(location)}" target="_blank" class="text-decoration-none">Search on OpenStreetMap</a>`;
                    if (mapMessage) mapMessage.innerHTML = '<div class="alert alert-warning rounded-3"><p class="mb-0">' + msg + '</p></div>';
                }
            })
            .catch(error => {
                console.error('Geocoding error:', error);
                const msg = 'Unable to load map. <a href="https://www.openstreetmap.org/search?query=' + encodeURIComponent(location) + '" target="_blank" class="text-decoration-none">View manually</a>.';
                if (mapMessage) mapMessage.innerHTML = '<div class="alert alert-danger rounded-3"><p class="mb-0">' + msg + '</p></div>';
            });
    });

    // AI Recommendations
    @auth
        document.addEventListener('DOMContentLoaded', function() {
            const userId = {{ auth()->id() }};
            const apiUrl = `http://localhost:5000/recommend/${userId}`;
            const recList = document.getElementById('ai-rec-list');

            fetch(apiUrl)
                .then(response => !response.ok ? Promise.reject(new Error('API error: ' + response.status)) : response.json())
                .then(data => {
                    if (data.recommendations && data.recommendations.length > 0) {
                        recList.innerHTML = data.recommendations.slice(0, 3).map(rec => {
                            // Check if it's a dataset event and build the appropriate URL
                            const eventUrl = rec.is_dataset 
                                ? `${Laravel.baseURL}/dataset-event/${rec.event_id}` 
                                : `${Laravel.baseURL}/event/${rec.event_id || rec.title.toLowerCase().replace(/[^a-z0-9]+/g, '-')}`;
                            
                            const badge = rec.is_dataset 
                                ? '<span class="badge bg-info text-white ms-2" style="font-size: 0.65rem;">Demo</span>' 
                                : '';
                            
                            return `
                            <div class="ai-rec-item p-3 mb-3 rounded-3 border-start border-3 border-success bg-white">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="flex-grow-1 me-3">
                                        <h6 class="fw-bold mb-1 text-dark">${rec.title}${badge}</h6>
                                        <small class="text-muted d-block mb-1">${rec.location} • ${rec.category || rec.plant_step}</small>
                                        <div class="predicted-score">Match Score: ${rec.predicted_rating.toFixed(1)}/5</div>
                                    </div>
                                    <a href="${eventUrl}" class="btn btn-outline-success btn-sm rounded-pill">View</a>
                                </div>
                            </div>
                        `;
                        }).join('');
                    } else {
                        recList.innerHTML = '<div class="text-center py-4"><i class="fa fa-thumbs-up fa-2x text-muted mb-2"></i><p class="text-muted">No recommendations yet. Explore more to get personalized suggestions!</p></div>';
                    }
                })
                .catch(error => {
                    console.error('Recommendation error:', error);
                    recList.innerHTML = '<div class="text-center py-4"><i class="fa fa-exclamation-triangle fa-2x text-warning mb-2"></i><p class="text-muted">Unable to load recommendations. <a href="{{ route("front.event") }}" class="text-green">Browse all events</a></p></div>';
                });
        });
    @else
        document.addEventListener('DOMContentLoaded', function() {
            const recList = document.getElementById('ai-rec-list');
            recList.innerHTML = '<div class="text-center py-4"><i class="fa fa-lock fa-2x text-muted mb-2"></i><p class="text-muted"><a href="{{ route("login") }}" class="text-green">Log in</a> to unlock personalized recommendations!</p></div>';
        });
    @endauth
</script>
@endsection