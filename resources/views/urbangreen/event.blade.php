{{-- resources/views/urbangreen/event.blade.php --}}
@extends('urbangreen.layouts.main')

@section('content')
<!-- Breadcrumb Area -->
<div class="breadcrumb-area bg-dark text-white position-relative overflow-hidden" style="background-image: linear-gradient(135deg, rgba(40, 167, 69, 0.8), rgba(0,0,0,0.7)), url({{ asset('urbangreen/img/bg-img/24.jpg') }}); background-size: cover; background-position: center;">
    <div class="container py-5 position-relative">
        <div class="row align-items-center">
            <div class="col-12 text-center">
                <h1 class="display-4 fw-bold mb-3 text-shadow">Upcoming Events</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center bg-transparent mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('front.home') }}" class="text-white text-decoration-none"><i class="fa fa-home me-1"></i> Home</a></li>
                        <li class="breadcrumb-item active text-light fw-semibold" aria-current="page">Events</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Events Section -->
<section class="section-padding-100 bg-light">
    <div class="container">
        @if(session('status'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4 border-0" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa fa-check-circle me-2 text-success fs-5"></i>
                    <div>{{ session('status') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4 border-0" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fa fa-exclamation-triangle me-2 text-danger fs-5"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Section Header -->
        <div class="row mb-5 text-center">
            <div class="col-12">
                <h2 class="fw-bold display-5 mb-3 text-green">Upcoming Events</h2>
                <p class="lead text-muted lh-lg mx-auto" style="max-width: 600px;">Discover and join community events to make our cities greener and more sustainable. Your participation matters!</p>
            </div>
        </div>

        <!-- Controls: Search, Filter, Sort -->
        <div class="row mb-4 justify-content-center">
            <div class="col-lg-10">
                <div class="bg-white rounded-4 p-4 shadow-lg d-flex flex-column flex-md-row gap-3 align-items-stretch align-items-md-center filter-bar">
                    <!-- Search -->
                    <div class="flex-grow-1 position-relative">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 ps-3 pe-0"><i class="fa fa-search text-muted"></i></span>
                            <input type="text" id="searchInput" class="form-control border-start-0 shadow-none ps-4 pe-0" placeholder="Search events by title, description, location, category, or organizer..." value="{{ request('search') }}">
                        </div>
                        <div id="searchLoading" class="position-absolute end-0 top-50 translate-middle-y me-3 d-none">
                            <div class="spinner-border spinner-border-sm text-success" role="status" style="width: 1rem; height: 1rem;">
                                <span class="visually-hidden">Searching...</span>
                            </div>
                        </div>
                    </div>
                    <!-- Filter -->
                    <div class="position-relative">
                        <select id="categoryFilter" class="form-select rounded-3 shadow-sm" style="min-width: 200px;">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <i class="fa fa-filter position-absolute end-0 top-50 translate-middle-y me-3 text-muted"></i>
                        <div id="filterLoading" class="position-absolute end-0 top-50 translate-middle-y me-3 d-none ms-4">
                            <div class="spinner-border spinner-border-sm text-success" role="status" style="width: 0.8rem; height: 0.8rem;">
                                <span class="visually-hidden">Filtering...</span>
                            </div>
                        </div>
                    </div>
                    <!-- Sort -->
                    <div class="position-relative">
                        <select id="sortSelect" class="form-select rounded-3 shadow-sm" style="min-width: 150px;">
                            <option value="relevance_desc" {{ request('sort', 'date_desc') == 'relevance_desc' ? 'selected' : '' }}>Relevance</option>
                            <option value="date_desc" {{ request('sort', 'date_desc') == 'date_desc' ? 'selected' : '' }}>Newest First</option>
                            <option value="date_asc" {{ request('sort') == 'date_asc' ? 'selected' : '' }}>Oldest First</option>
                            <option value="attendees_desc" {{ request('sort') == 'attendees_desc' ? 'selected' : '' }}>Most Popular</option>
                            <option value="title_asc" {{ request('sort') == 'title_asc' ? 'selected' : '' }}>A-Z</option>
                        </select>
                        <i class="fa fa-sort position-absolute end-0 top-50 translate-middle-y me-3 text-muted"></i>
                        <div id="sortLoading" class="position-absolute end-0 top-50 translate-middle-y me-3 d-none ms-4">
                            <div class="spinner-border spinner-border-sm text-success" role="status" style="width: 0.8rem; height: 0.8rem;">
                                <span class="visually-hidden">Sorting...</span>
                            </div>
                        </div>
                    </div>
                    <!-- Clear Button -->
                    <button id="clearBtn" class="btn btn-success rounded-3 shadow-sm px-4 d-flex align-items-center justify-content-center d-none" style="min-width: 100px;">
                        <i class="fa fa-times me-1"></i>Clear
                    </button>
                    <!-- Apply Button for mobile -->
                    <button id="applyFilters" class="btn btn-success rounded-3 fw-semibold d-md-none" style="min-width: 100px;">Apply</button>
                </div>
            </div>
        </div>

        <!-- Events Grid -->
        <div class="row g-4 mb-5" id="events-grid">
            @forelse ($events as $index => $event)
                <div class="col-lg-4 col-md-6 fade-in" style="animation-delay: {{ $index * 0.05 }}s;">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden modern-card position-relative" style="transition: all 0.3s ease; background: linear-gradient(145deg, #ffffff, #f8f9fa);">
                        <!-- Event Image -->
                        <div class="position-relative overflow-hidden">
                            <img src="{{ $event->image ? (Storage::exists($event->image) ? Storage::url($event->image) : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjMjhhNzQ1Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNiIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBJbWFnZTwvdGV4dD48L3N2Zz4=') : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjMjhhNzQ1Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNiIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5FdmVudDwvdGV4dD48L3N2Zz4=' }}"
                                 class="card-img-top" alt="{{ $event->title }}" style="height: 200px; object-fit: cover; transition: transform 0.3s ease;">
                            @if ($event->event_date->isFuture())
                                <span class="position-absolute top-2 start-2 bg-success text-white px-2 py-1 rounded-pill fw-semibold badge-upcoming text-xs shadow" style="font-size: 0.7rem;">Upcoming</span>
                            @endif
                            @if($event->is_published === false)
                                <span class="position-absolute top-2 end-2 bg-secondary text-white px-2 py-1 rounded-pill fw-semibold shadow text-xs" style="font-size: 0.7rem;">Draft</span>
                            @endif
                            <div class="position-absolute bottom-2 end-2 p-2">
                                <span class="badge bg-light text-dark rounded-pill px-3 py-1 fw-semibold text-xs d-flex align-items-center"><i class="fa fa-users me-1"></i>{{ $event->users_count }}</span>
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column p-4" style="min-height: 220px;">
                            <!-- Title -->
                            <h5 class="card-title fw-bold mb-2 text-dark lh-sm" style="font-size: 1.1rem;">{{ $event->title }}</h5>

                            <!-- Category -->
                            @if($event->category)
                                <span class="badge bg-success rounded-pill px-3 py-2 mb-3 fw-semibold text-xs" style="background: linear-gradient(135deg, #28a745, #20c997);">
                                    {{ is_object($event->category) ? $event->category->name : ($event->category ?: 'Uncategorized') }}
                                </span>
                            @endif

                            <!-- Details -->
                            <div class="text-muted small mb-2 d-flex align-items-center">
                                <i class="fa fa-calendar-alt me-2 text-green fs-6"></i><span class="fw-medium">{{ $event->event_date->format('M j, Y g:i A') }}</span>
                            </div>
                            <div class="text-muted small mb-3 d-flex align-items-center">
                                <i class="fa fa-map-marker-alt me-2 text-green fs-6"></i><span class="fw-medium text-truncate">{{ $event->location }}</span>
                            </div>

                            <!-- Description -->
                            <p class="card-text text-muted flex-grow-1 lh-lg mb-3" style="font-size: 0.9rem;">{{ \Illuminate\Support\Str::limit($event->description, 100) }}</p>

                            <!-- Organizer -->
                            @if($event->user)
                                <div class="d-flex align-items-center mb-4 text-xs border-top pt-2">
                                    <img src="{{ $event->user->profile_photo ? (Storage::exists($event->user->profile_photo) ? Storage::url($event->user->profile_photo) : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iIzI4YTc0NSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+VTwvdGV4dD48L3N2Zz4=') : 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iIzI4YTc0NSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+VTwvdGV4dD48L3N2Zz4=' }}" class="rounded-circle me-2 shadow" style="width: 32px; height: 32px; object-fit: cover;" alt="Organizer">
                                    <div>
                                        <span class="fw-semibold text-green d-block" style="font-size: 0.85rem;">{{ \Illuminate\Support\Str::limit($event->user->name, 20) }}</span>
                                        <small class="text-muted">Organizer</small>
                                    </div>
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="d-flex gap-2 mt-auto">
                                <a href="{{ route('front.event.show', $event) }}" class="btn btn-outline-primary rounded-3 flex-grow-1 shadow-none py-2 fw-semibold" style="font-size: 0.85rem; border: 1px solid #28a745;">
                                    <i class="fa fa-eye me-1"></i> View Details
                                </a>
                                @auth
                                    @if($event->users->contains(auth()->id()))
                                        <button class="btn btn-success rounded-3 shadow-none py-2" disabled style="font-size: 0.85rem; min-width: 50px;"><i class="fa fa-check"></i></button>
                                    @else
                                        <form action="{{ route('front.event.enroll', $event) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success rounded-3 shadow-none py-2 fw-semibold" style="font-size: 0.85rem; min-width: 50px;"><i class="fa fa-plus"></i></button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-success rounded-3 shadow-none py-2 fw-semibold" style="font-size: 0.85rem; min-width: 50px;"><i class="fa fa-sign-in-alt"></i></a>
                                @endauth
                            </div>

                            <!-- QR Code -->
                            <div class="text-center pt-3 mt-3 border-top">
                                <div class="qr-container d-inline-block p-2 rounded-3 shadow bg-white" style="border: 1px solid #e9ecef;">
                                    {!! QrCode::format('svg')->size(60)->errorCorrection('H')->generate(route('front.event.show', $event)) !!}
                                </div>
                                <small class="text-muted d-block mt-1">Scan for quick access</small>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-6">
                    <i class="fa fa-calendar-times fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted mb-3">No Events Found</h4>
                    <p class="lead text-muted">No events match your criteria. Try adjusting your search or check back soon!</p>
                    <a href="{{ route('front.event') }}" class="btn btn-success rounded-pill px-4 py-2 mt-3 shadow">Browse All Events</a>
                </div>
            @endforelse
        </div>

        <!-- Loading Indicator -->
        <div id="loading" class="text-center py-4 d-none">
            <div class="spinner-border text-success spinner-border-lg" role="status">
                <span class="visually-hidden">Loading more events...</span>
            </div>
            <p class="text-muted mt-3 small">Loading more events...</p>
        </div>

        <!-- End of Events -->
        <div id="no-more" class="text-center py-4 d-none">
            <i class="fa fa-check-circle fa-3x text-success mb-3"></i>
            <p class="text-muted h6">No more events to load</p>
        </div>

        <!-- AI Recommendations -->
        @auth
            <div class="row mb-5">
                <div class="col-12">
                    <div class="card border-0 shadow-lg rounded-4">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="fw-bold text-green mb-1 d-flex align-items-center"><i class="fa fa-brain me-2"></i>Recommended for You</h5>
                            <small class="text-muted">Based on your activity</small>
                        </div>
                        <div class="card-body pt-3" id="ai-rec-grid">
                            <div class="text-center py-4">
                                <div class="spinner-border text-success spinner-border-sm mb-2" role="status"></div>
                                <p class="text-muted mb-0">Personalizing your recommendations...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endauth
    </div>
</section>

<!-- Enhanced Custom CSS for Professional UI -->
<style>
    :root {
        --green-primary: #28a745;
        --green-light: #d4edda;
        --shadow-sm: 0 2px 10px rgba(0,0,0,0.08);
        --shadow-md: 0 8px 25px rgba(0,0,0,0.12);
        --shadow-lg: 0 16px 40px rgba(0,0,0,0.15);
    }
    .text-green { color: var(--green-primary) !important; }
    .text-shadow { text-shadow: 0 2px 4px rgba(0,0,0,0.3); }
    .filter-bar {
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
        position: sticky;
        top: 100px;
        z-index: 1020;
        backdrop-filter: blur(10px);
    }
    .filter-bar:focus-within {
        border-color: var(--green-primary);
        box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.15);
    }
    .filter-bar .form-select,
    .filter-bar .form-control {
        border: none !important;
        box-shadow: none !important;
    }
    .filter-bar .form-select:focus,
    .filter-bar .form-control:focus {
        border: none !important;
        box-shadow: none !important;
    }
    .filter-bar .position-absolute {
        pointer-events: none;
        z-index: 5;
    }
    .modern-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .modern-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: var(--shadow-lg) !important;
    }
    .modern-card .card-img-top:hover { 
        transform: scale(1.1); 
    }
    .badge-upcoming {
        background: linear-gradient(135deg, var(--green-primary), #20c997) !important;
        animation: pulse 2s infinite;
        box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.05); }
    }
    .fade-in {
        animation: fadeInUp 0.8s ease forwards;
        opacity: 0;
        transform: translateY(40px);
    }
    @keyframes fadeInUp {
        to { opacity: 1; transform: translateY(0); }
    }
    .ai-rec-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border-radius: 1rem;
        border: 1px solid rgba(0,0,0,0.05);
    }
    .ai-rec-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }
    .qr-container { 
        transition: all 0.3s ease; 
        border-radius: 0.75rem;
    }
    .qr-container:hover { 
        transform: scale(1.1); 
        box-shadow: var(--shadow-md); 
    }
    .btn { 
        transition: all 0.3s ease; 
        font-weight: 600; 
        box-shadow: var(--shadow-sm); 
    }
    .btn:hover { 
        transform: translateY(-2px); 
        box-shadow: var(--shadow-md); 
    }
    @media (max-width: 768px) {
        .filter-bar {
            flex-direction: column !important;
            gap: 1rem !important;
            top: 20px;
        }
        .card-body { padding: 1.25rem !important; }
        .modern-card:hover { transform: none; }
        .d-flex.gap-2 .btn { font-size: 0.75rem; padding: 0.5rem; }
    }
</style>

<!-- JS for Infinite Scroll, Filtering, Sorting -->
<script>
    window.Laravel = { baseURL: '{{ url("") }}' };
    let currentPage = 1;
    let isLoading = false;
    let hasMore = {{ $events->hasMorePages() }};
    let currentSearch = '{{ request("search") ?? "" }}';
    let currentCategory = '{{ request("category") ?? "" }}';
    let currentSort = '{{ request("sort", "date_desc") }}';
    const perPage = 6; // Match controller paginate(6)

    // Update URL on filter/sort change
    function updateURL(params) {
        const url = new URL(window.location);
        Object.keys(params).forEach(key => {
            if (params[key]) url.searchParams.set(key, params[key]);
            else url.searchParams.delete(key);
        });
        window.history.pushState({}, '', url);
    }

    // Update Clear Button Visibility
    function updateClearBtnVisibility() {
        const clearBtn = document.getElementById('clearBtn');
        if (clearBtn) {
            const hasFilters = currentSearch.trim() !== '' || currentCategory !== '' || currentSort !== 'date_desc';
            clearBtn.classList.toggle('d-none', !hasFilters);
        }
    }

    // Load more events via infinite scroll
    function loadMore() {
        if (isLoading || !hasMore) return;
        isLoading = true;
        const loadingEl = document.getElementById('loading');
        loadingEl.classList.remove('d-none');

        fetch(`{{ route('front.event') }}?page=${++currentPage}&search=${encodeURIComponent(currentSearch)}&category=${currentCategory}&sort=${currentSort}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newEvents = Array.from(doc.querySelectorAll('#events-grid > div[class*="col-"]:not(.col-12)'));
                const grid = document.getElementById('events-grid');

                newEvents.forEach(el => grid.appendChild(el.cloneNode(true)));
                hasMore = newEvents.length === perPage;
                document.getElementById('no-more').classList.toggle('d-none', hasMore);
                loadingEl.classList.add('d-none');
                isLoading = false;

                // Re-init animations for new elements
                newEvents.forEach((el, idx) => {
                    el.classList.add('fade-in');
                    el.style.animationDelay = `${idx * 0.05}s`;
                });
            })
            .catch(() => {
                loadingEl.classList.add('d-none');
                isLoading = false;
            });
    }

    // Intersection Observer for infinite scroll
    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && hasMore) loadMore();
    }, { threshold: 0.1 });
    if (document.getElementById('loading')) {
        observer.observe(document.getElementById('loading'));
    }

    // Reload events on filter/sort
    function reloadEvents(showLoading = true) {
        if (showLoading) {
            const grid = document.getElementById('events-grid');
            grid.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        }
        fetch(`{{ route('front.event') }}?search=${encodeURIComponent(currentSearch)}&category=${currentCategory}&sort=${currentSort}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const grid = document.getElementById('events-grid');
                grid.innerHTML = doc.getElementById('events-grid').innerHTML;

                const eventCount = grid.querySelectorAll('div[class*="col-"][class*="fade-in"]').length;
                hasMore = eventCount === perPage;
                currentPage = 1;

                document.getElementById('no-more').classList.toggle('d-none', hasMore);

                // Hide all filter loadings and reset visual feedback
                document.getElementById('searchLoading').classList.add('d-none');
                document.getElementById('filterLoading').classList.add('d-none');
                document.getElementById('sortLoading').classList.add('d-none');
                const categoryFilter = document.getElementById('categoryFilter');
                const sortSelect = document.getElementById('sortSelect');
                if (categoryFilter) categoryFilter.classList.remove('text-muted');
                if (sortSelect) sortSelect.classList.remove('text-muted');

                // Re-init observer and animations
                if (document.getElementById('loading')) {
                    observer.observe(document.getElementById('loading'));
                }
                document.querySelectorAll('.fade-in').forEach((el, index) => {
                    el.style.animationDelay = `${index * 0.05}s`;
                });
            })
            .catch(error => {
                console.error('Reload error:', error);
                // Hide loadings even on error
                document.getElementById('searchLoading').classList.add('d-none');
                document.getElementById('filterLoading').classList.add('d-none');
                document.getElementById('sortLoading').classList.add('d-none');
                const categoryFilter = document.getElementById('categoryFilter');
                const sortSelect = document.getElementById('sortSelect');
                if (categoryFilter) categoryFilter.classList.remove('text-muted');
                if (sortSelect) sortSelect.classList.remove('text-muted');
                // Optionally show error message in grid
                const grid = document.getElementById('events-grid');
                grid.innerHTML = '<div class="col-12 text-center py-5"><i class="fa fa-exclamation-triangle fa-3x text-warning mb-3"></i><p class="text-muted">Failed to load events. <a href="#" onclick="location.reload()" class="text-green">Retry</a></p></div>';
            });
    }

    // Debounce utility
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Fade-in for initial elements
        document.querySelectorAll('.fade-in').forEach((el, index) => {
            el.style.animationDelay = `${index * 0.05}s`;
        });

        // Update initial clear button visibility
        updateClearBtnVisibility();

        // Clear button listener
        const clearBtn = document.getElementById('clearBtn');
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                document.getElementById('searchInput').value = '';
                document.getElementById('categoryFilter').value = '';
                document.getElementById('sortSelect').value = 'date_desc';
                currentSearch = '';
                currentCategory = '';
                currentSort = 'date_desc';
                updateURL({ search: '', category: '', sort: 'date_desc' });
                updateClearBtnVisibility();
                reloadEvents();
            });
        }

        // Filter/Sort listeners
        const searchInput = document.getElementById('searchInput');
        const searchLoading = document.getElementById('searchLoading');
        const debouncedSearch = debounce(function(e) {
            currentSearch = e.target.value;
            updateClearBtnVisibility();
            updateURL({ search: currentSearch, category: currentCategory, sort: currentSort });
            searchLoading.classList.remove('d-none');
            reloadEvents(true);
        }, 300);
        searchInput.addEventListener('input', debouncedSearch);

        const categoryFilter = document.getElementById('categoryFilter');
        const filterLoading = document.getElementById('filterLoading');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', function(e) {
                currentCategory = e.target.value;
                updateClearBtnVisibility();
                updateURL({ search: currentSearch, category: currentCategory, sort: currentSort });
                filterLoading.classList.remove('d-none');
                categoryFilter.classList.add('text-muted');
                reloadEvents(true);
            });
        }

        const sortSelect = document.getElementById('sortSelect');
        const sortLoading = document.getElementById('sortLoading');
        if (sortSelect) {
            sortSelect.addEventListener('change', function(e) {
                currentSort = e.target.value;
                updateClearBtnVisibility();
                updateURL({ search: currentSearch, category: currentCategory, sort: currentSort });
                sortLoading.classList.remove('d-none');
                sortSelect.classList.add('text-muted');
                reloadEvents(true);
            });
        }

        // Mobile apply button
        const applyFilters = document.getElementById('applyFilters');
        if (applyFilters) {
            applyFilters.addEventListener('click', function() {
                const search = document.getElementById('searchInput').value;
                const category = document.getElementById('categoryFilter').value;
                const sort = document.getElementById('sortSelect').value;

                if (search !== currentSearch) {
                    currentSearch = search;
                }
                if (category !== currentCategory) {
                    currentCategory = category;
                }
                if (sort !== currentSort) {
                    currentSort = sort;
                }
                updateClearBtnVisibility();
                updateURL({ search: currentSearch, category: currentCategory, sort: currentSort });
                reloadEvents(true);
            });
        }
    });
</script>

<!-- AI Recommendations Script -->
@auth
<script>
    // Ensure Laravel is defined (fallback in case of script loading order issues)
    window.Laravel = window.Laravel || { baseURL: '{{ url("") }}' };

    document.addEventListener('DOMContentLoaded', function() {
        const userId = {{ auth()->id() }};
        const apiUrl = `http://localhost:5000/recommend/${userId}`;
        const recGrid = document.getElementById('ai-rec-grid');

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('API error: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.recommendations && data.recommendations.length > 0) {
                    recGrid.innerHTML = `
                        <div class="row g-3">
                            ${data.recommendations.slice(0, 6).map(rec => {
                                const demoBadge = rec.is_dataset 
                                    ? '<span class="badge bg-info position-absolute top-0 end-0 m-2" style="font-size: 0.7rem; z-index: 10;">Demo</span>' 
                                    : '';
                                return `
                                <div class="col-md-6 col-lg-4">
                                    <div class="card h-100 border-0 shadow-sm rounded-3 ai-rec-card text-decoration-none text-dark position-relative" data-event-id="${rec.event_id}">
                                        ${demoBadge}
                                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjEyMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjMjhhNzQ1Ii8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxNCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5FdmVudDwvdGV4dD48L3N2Zz4=" class="card-img-top rounded-top-3" alt="${rec.title}" style="height: 120px; object-fit: cover;">
                                        <div class="card-body p-2">
                                            <h6 class="card-title fw-bold mb-1 text-truncate text-dark" style="font-size: 0.9rem;">${rec.title}</h6>
                                            <p class="card-text small text-muted mb-2 text-truncate">${rec.location} • ${rec.category || rec.plant_step}</p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="predicted-score text-xs">Score: ${rec.predicted_rating.toFixed(1)}/5</span>
                                                <i class="fa fa-arrow-right text-green"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            }).join('')}
                        </div>
                    `;

                    // Add click handlers for AI rec cards (direct redirect with real ID or dataset ID)
                    document.querySelectorAll('.ai-rec-card').forEach((card, index) => {
                        card.addEventListener('click', function() {
                            const eventId = this.dataset.eventId;
                            const rec = data.recommendations[index];
                            // Check if it's a dataset event
                            if (rec.is_dataset) {
                                window.location.href = window.Laravel.baseURL + '/dataset-event/' + eventId;
                            } else {
                                window.location.href = window.Laravel.baseURL + '/event/' + eventId;
                            }
                        });
                    });
                } else {
                    recGrid.innerHTML = '<div class="text-center py-4"><i class="fa fa-thumbs-up fa-2x text-muted mb-3"></i><p class="text-muted">No recommendations yet. Join more events!</p></div>';
                }
            })
            .catch(error => {
                console.error('Recommendation error:', error);
                recGrid.innerHTML = '<div class="text-center py-4"><i class="fa fa-exclamation-triangle fa-2x text-warning mb-3"></i><p class="text-muted">Unable to load recommendations. <a href="#" class="text-green" onclick="location.reload();">Retry</a></p></div>';
            });
    });
</script>
@endauth
@endsection