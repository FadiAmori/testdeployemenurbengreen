{{-- resources/views/dashboard/pages/events/index.blade.php --}}
<x-dashboard::layout bodyClass="g-sidenav-show bg-gray-200">
    <x-dashboard::navbars.sidebar activePage="event" />
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-dashboard::navbars.navs.auth titlePage="Events Management" />
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="text-gradient text-primary mb-0">Events</h2>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.event.create') }}" class="btn btn-primary btn-sm rounded-pill px-4">
                                <i class="fa fa-plus me-2"></i>New Event
                            </a>
                            <a href="{{ route('admin.event.export') }}" class="btn btn-outline-success btn-sm rounded-pill px-4">
                                <i class="fa fa-download me-2"></i>Export CSV
                            </a>
                            <a href="{{ route('admin.event.export-excel') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                                <i class="fa fa-file-excel me-2"></i>Export Excel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Tabs for List vs Calendar -->
            <ul class="nav nav-tabs nav-pills mb-4" id="eventTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="list-tab" data-bs-toggle="pill" data-bs-target="#list" type="button" role="tab">Event List</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="calendar-tab" data-bs-toggle="pill" data-bs-target="#calendar" type="button" role="tab">Calendar View</button>
                </li>
            </ul>

            <div class="tab-content" id="eventTabsContent">
                <!-- List Tab (Existing Table) -->
                <div class="tab-pane fade show active" id="list" role="tabpanel">
                    <!-- Search & Filters -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.event.index') }}" class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Search Events</label>
                                    <input type="text" name="search" class="form-control" placeholder="Title or description..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <select name="published" class="form-select">
                                        <option value="">All</option>
                                        <option value="1" {{ request('published') == '1' ? 'selected' : '' }}>Published</option>
                                        <option value="0" {{ request('published') == '0' ? 'selected' : '' }}>Draft</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Category</label>
                                    <select name="category" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach($categories ?? [] as $cat)
                                            <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary w-100 rounded-pill">
                                        <i class="fa fa-search me-2"></i>Filter
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Events Table -->
                    <div class="card">
                        <div class="card-header pb-0 p-3">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-0">Event List</h6>
                                <div class="input-group input-group-outline w-25">
                                    <label class="form-label">Quick Search</label>
                                    <input type="text" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Location</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Category</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Attendees</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($events as $event)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2">
                                                        <div>
                                                            <img src="{{ $event->image ? Storage::url($event->image) : asset('assets/img/default-event.jpg') }}" class="avatar avatar-sm rounded-circle me-2" alt="{{ $event->title }}">
                                                        </div>
                                                        <div class="my-auto">
                                                            <h6 class="mb-0 text-sm">{{ $event->title }}</h6>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="text-xs font-weight-bold">{{ $event->event_date->format('M d, Y') }}</span>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ Str::limit($event->location, 20) }}</p>
                                                </td>
                                                <td>
                                                    @if($event->category)
                                                        <span class="badge badge-sm bg-gradient-success">{{ $event->category->name }}</span>
                                                    @else
                                                        <span class="badge badge-sm bg-gradient-secondary">No Category</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="text-xs font-weight-bold badge badge-sm bg-gradient-info">{{ $event->users_count }} / {{ $event->max_participants ?? 'Unlimited' }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-sm {{ $event->is_published ? 'bg-gradient-success' : 'bg-gradient-warning' }}">
                                                        {{ $event->is_published ? 'Published' : 'Draft' }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('admin.event.show', $event) }}" class="btn btn-link text-dark btn-sm p-0 mx-1" data-bs-toggle="tooltip" title="View Attendance">
                                                        <i class="fa fa-eye text-secondary"></i>
                                                    </a>
                                                    <a href="{{ route('admin.event.edit', $event) }}" class="btn btn-link text-dark btn-sm p-0 mx-1" data-bs-toggle="tooltip" title="Edit">
                                                        <i class="fa fa-edit text-secondary"></i>
                                                    </a>
                                                    <form action="{{ route('admin.event.destroy', $event) }}" method="POST" class="d-inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-link text-danger btn-sm p-0 mx-1" onclick="return confirm('Delete this event?')" data-bs-toggle="tooltip" title="Delete">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <i class="fa fa-calendar-times fa-2x text-muted mb-3"></i>
                                                    <p class="text-muted">No events found.</p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer px-3">
                            {{ $events->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>

                <!-- Calendar Tab -->
                <div class="tab-pane fade" id="calendar" role="tabpanel">
                    <!-- Calendar Filters -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form id="calendarFilter" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Filter by Category</label>
                                    <select id="categoryFilter" class="form-select">
                                        <option value="">All Categories</option>
                                        @foreach($categories ?? [] as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select id="statusFilter" class="form-select">
                                        <option value="">All Status</option>
                                        <option value="published">Published</option>
                                        <option value="draft">Draft</option>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" id="applyFilters" class="btn btn-success rounded-pill px-4">Apply Filters</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- FullCalendar Container -->
                    <div class="card">
                        <div class="card-header pb-0 p-3">
                            <h6 class="mb-0">Event Calendar</h6>
                        </div>
                        <div class="card-body p-0">
                            <div id="calendar" style="height: 600px; background: #f8f9fa;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-dashboard::layout>

<style>
    .avatar-sm { width: 36px; height: 36px; }
    .bg-gradient-success { background: linear-gradient(195deg, #66bb6a, #43a047) !important; }
    .bg-gradient-warning { background: linear-gradient(195deg, #ffb74d, #ff9800) !important; }
    .bg-gradient-info { background: linear-gradient(195deg, #26c6da, #00acc1) !important; }
    .text-gradient { background: linear-gradient(195deg, #49a6fc, #1ea5fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    #calendar .fc-event { border: none !important; border-radius: 0.375rem !important; }
    #calendar .fc-event:hover { transform: scale(1.05); transition: transform 0.2s; }
    .fc-theme-standard td.fc-today { background-color: #d4edda !important; }
</style>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet" />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let calendar = null;
        let calendarRendered = false;
        const calendarEl = document.getElementById('calendar');

        function createCalendar() {
            if (!calendarEl || calendar) return;

            calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: '{{ route("admin.event.calendar-data") }}',
                eventClick: function(info) {
                    if (info.event && info.event.url) {
                        window.location.href = info.event.url;
                    }
                },
                eventDidMount: function(info) {
                    if (info.el) info.el.style.cursor = 'pointer';
                    if (info.event && info.event.extendedProps) info.el.title = info.event.extendedProps.description || '';
                },
                eventColor: '#28a745',
                height: 'auto',
                aspectRatio: 1.8,
                editable: true,
                dayMaxEvents: 3,
                eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false }
            });
        }

        function renderCalendarIfVisible() {
            if (!calendarEl) return;
            const isVisible = calendarEl.offsetParent !== null; // quick visibility check
            if (!calendar) createCalendar();
            if (!calendar) return;
            if (isVisible && !calendarRendered) {
                calendar.render();
                calendarRendered = true;
            } else if (isVisible && calendarRendered) {
                // ensure size is correct after becoming visible
                calendar.updateSize();
            }
        }

        // When the Calendar tab is shown, render/update the calendar
        document.addEventListener('shown.bs.tab', function(e) {
            try {
                const activated = e.target && (e.target.getAttribute('data-bs-target') || e.target.getAttribute('href'));
                console.debug('Tab shown event, activated:', activated);
                if (activated === '#calendar') {
                    // Delay slightly to ensure Bootstrap finished layout
                    setTimeout(renderCalendarIfVisible, 50);
                }
            } catch (err) {
                console.debug('Error handling shown.bs.tab for calendar:', err);
            }
        });

        // If the calendar tab is active on load, render it; otherwise create instance so events can be refetched later
        const activeTab = document.querySelector('#eventTabs .nav-link.active');
        if (activeTab && activeTab.getAttribute('data-bs-target') === '#calendar') {
            createCalendar();
            renderCalendarIfVisible();
        } else {
            // create but don't render until tab shown
            createCalendar();
        }

        // Resize handler to keep calendar sizing correct
        window.addEventListener('resize', function() {
            if (calendar && calendarRendered) calendar.updateSize();
        });

        // Filter button: refetch events (server should accept filter params if implemented)
        const applyBtn = document.getElementById('applyFilters');
        if (applyBtn) {
            applyBtn.addEventListener('click', function() {
                if (calendar) {
                    calendar.refetchEvents();
                }
            });
        }
    });
</script>