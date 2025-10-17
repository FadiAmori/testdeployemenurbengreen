{{-- resources/views/dashboard/pages/events/index.blade.php --}}
<x-dashboard::layout bodyClass="bg-gray-200" titlePage="Events" activePage="events" :showSidebar="true">
    @push('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @endpush

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    {{-- Header with Buttons --}}
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center bg-white border-0">
                        <h6 class="mb-0 fw-bold text-dark">Manage Events</h6>
                        <div class="d-flex gap-2">
                            <a href="{{ route('admin.event.create') }}" class="btn btn-sm bg-gradient-success text-white">
                                <i class="bi bi-plus-circle me-1"></i>Create Event
                            </a>
                            <a href="{{ route('admin.event.export-pdf', request()->query()) }}" class="btn btn-sm bg-gradient-primary text-white" target="_blank">
                                <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
                            </a>
                        </div>
                    </div>

                    {{-- Filters --}}
                    <div class="card-body px-4 pb-0">
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form method="GET" class="row g-3 mb-4 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Search</label>
                                <input type="text" name="search" class="form-control" placeholder="Title or description..." value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Category</label>
                                <select name="category" class="form-select">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="published" class="form-select">
                                    <option value="">All</option>
                                    <option value="1" {{ request('published') == '1' ? 'selected' : '' }}>Published</option>
                                    <option value="0" {{ request('published') == '0' ? 'selected' : '' }}>Draft</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                                @if(request()->filled('search') || request()->filled('category') || request()->filled('published'))
                                    <a href="{{ route('admin.event.index') }}" class="btn btn-outline-secondary btn-sm w-100 mt-1">Clear</a>
                                @endif
                            </div>
                        </form>

                        {{-- Tabs: Table vs Calendar --}}
                        <ul class="nav nav-tabs mb-3" id="adminEventTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list" type="button" role="tab">List</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab">Calendar</button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="list" role="tabpanel">
                                {{-- Table --}}
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0">Title</th>
                                        <th class="border-0">Date</th>
                                        <th class="border-0">Location</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($events as $event)
                                        <tr class="hover-shadow">
                                            <td>
                                                <div>
                                                    <h6 class="mb-0 fw-semibold">{{ Str::limit($event->title, 40) }}</h6>
                                                    <small class="text-muted">{{ Str::limit($event->description ?? 'No description', 60) }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $event->event_date->format('M j, Y') }}</span>
                                                <br><small class="text-muted">{{ $event->event_date->format('g:i A') }}</small>
                                            </td>
                                            <td>{{ $event->location }}</td>
                                            <td>
                                                <span class="badge {{ $event->is_published ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $event->is_published ? 'Published' : 'Draft' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="{{ route('admin.event.show', $event) }}" class="btn btn-sm btn-info text-white rounded-pill mx-1 action-btn" title="View">
                                                        <i class="bi bi-eye"></i>
                                                        <span class="ms-1 d-none d-md-inline">View</span>
                                                    </a>
                                                    <a href="{{ route('admin.event.edit', $event) }}" class="btn btn-sm btn-primary text-white rounded-pill mx-1 action-btn" title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                        <span class="ms-1 d-none d-md-inline">Edit</span>
                                                    </a>
                                                    <form action="{{ route('admin.event.destroy', $event) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger text-white rounded-pill mx-1 action-btn" title="Delete">
                                                            <i class="bi bi-trash"></i>
                                                            <span class="ms-1 d-none d-md-inline">Delete</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5">
                                                <i class="bi bi-calendar-event fs-1 text-muted mb-3"></i>
                                                <h5 class="text-muted">No events found</h5>
                                                <p class="text-muted">Try adjusting your filters or create a new event.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                                </div>

                                {{-- Pagination --}}
                                @if($events->hasPages())
                                    <nav aria-label="Events pagination" class="mt-4">
                                        {{ $events->appends(request()->query())->links() }}
                                    </nav>
                                @endif
                            </div>

                            <div class="tab-pane fade" id="calendar" role="tabpanel">
                                <div class="card">
                                    <div class="card-header pb-0 d-flex justify-content-between align-items-center bg-white border-0">
                                        <h6 class="mb-0 fw-bold text-dark">Event Calendar</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div id="calendar" style="height: 600px; background: #f8f9fa;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
    <script>
        // Optional: Add hover effect for rows
        document.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('mouseenter', () => row.style.backgroundColor = '#f8f9fa');
            row.addEventListener('mouseleave', () => row.style.backgroundColor = 'transparent');
        });
    </script>

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet" />
    <!-- FullCalendar (only needed for calendar tab) -->
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
                const isVisible = calendarEl.offsetParent !== null;
                if (!calendar) createCalendar();
                if (!calendar) return;
                if (isVisible && !calendarRendered) {
                    calendar.render();
                    calendarRendered = true;
                } else if (isVisible && calendarRendered) {
                    calendar.updateSize();
                }
            }

            // Listen for tab show events (Bootstrap 5)
            document.addEventListener('shown.bs.tab', function(e) {
                try {
                    const activated = e.target && (e.target.getAttribute('data-bs-target') || e.target.getAttribute('href'));
                    console.debug('Tab shown event, activated:', activated);
                    if (activated === '#calendar') {
                        setTimeout(renderCalendarIfVisible, 50);
                    }
                } catch (err) {
                    console.debug('Error handling shown.bs.tab for calendar:', err);
                }
            });

            // If the calendar tab is active on load, render it
            const activeTab = document.querySelector('#adminEventTabs .nav-link.active');
            if (activeTab && activeTab.getAttribute('data-bs-target') === '#calendar') {
                createCalendar();
                renderCalendarIfVisible();
            } else {
                createCalendar();
            }

            window.addEventListener('resize', function() {
                if (calendar && calendarRendered) calendar.updateSize();
            });

        });
    </script>
    @endpush
    <style>
        /* Polished action button styling for event rows */
        .action-btn {
            padding: 0.35rem 0.65rem;
            font-size: 0.85rem;
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(16,24,40,0.06);
            text-decoration: none;
        }
    </style>
</x-dashboard::layout>
