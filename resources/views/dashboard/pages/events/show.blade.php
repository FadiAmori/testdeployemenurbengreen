{{-- resources/views/dashboard/pages/events/show.blade.php --}}
<x-dashboard::layout bodyClass="bg-gray-200" titlePage="Event Details" activePage="events" :showSidebar="true">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                {{-- Back Button --}}
                <div class="mb-3">
                    <a href="{{ route('admin.event.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Events
                    </a>
                </div>

                <div class="row g-4">
                    {{-- Main Event Details --}}
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0 fw-bold">
                                    <i class="bi bi-calendar-event me-2"></i>{{ $event->title }}
                                </h5>
                            </div>
                            <div class="card-body">
                                @if($event->image)
                                    <div class="text-center mb-4">
                                        <img src="{{ asset('storage/' . $event->image) }}" alt="{{ $event->title }}" class="img-fluid rounded shadow-sm" style="max-height: 300px; object-fit: cover;">
                                    </div>
                                @endif

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted">Description</label>
                                        <p class="text-dark">{{ $event->description ?? 'No description provided.' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted">Location</label>
                                        <p class="text-dark"><i class="bi bi-geo-alt me-1"></i>{{ $event->location }}</p>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="fw-semibold text-muted">Date & Time</label>
                                        <p class="text-dark">
                                            <i class="bi bi-clock me-1"></i>{{ $event->event_date->format('M j, Y \a\t g:i A') }}
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-semibold text-muted">Category</label>
                                        <p class="text-dark">
                                            <span class="badge bg-info">{{ $event->category?->name ?? 'Uncategorized' }}</span>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="fw-semibold text-muted">Status</label>
                                        <span class="badge {{ $event->is_published ? 'bg-success' : 'bg-warning' }}">
                                            {{ $event->is_published ? 'Published' : 'Draft' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted">Max Participants</label>
                                        <p class="text-dark">{{ $event->max_participants ?? 'Unlimited' }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="fw-semibold text-muted">Current Attendees</label>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $event->max_participants ? min(($event->users_count / $event->max_participants) * 100, 100) : 100 }}%" aria-valuenow="{{ $event->users_count }}" aria-valuemin="0" aria-valuemax="{{ $event->max_participants ?? 100 }}"></div>
                                        </div>
                                        <small class="text-muted">{{ $event->users_count }} / {{ $event->max_participants ?? 'Unlimited' }}</small>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="{{ route('admin.event.edit', $event) }}" class="btn btn-primary">
                                        <i class="bi bi-pencil me-1"></i>Edit Event
                                    </a>
                                    <button class="btn btn-outline-danger" onclick="confirmDelete()">
                                        <i class="bi bi-trash me-1"></i>Delete Event
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Attendees Sidebar --}}
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-header bg-light">
                                <h6 class="mb-0 fw-bold">
                                    <i class="bi bi-people me-2"></i>Attendees ({{ $event->users_count }})
                                </h6>
                            </div>
                            <div class="card-body">
                                @if($event->users->isEmpty())
                                    <div class="text-center py-4 text-muted">
                                        <i class="bi bi-person-plus fs-1 mb-2"></i>
                                        <p>No attendees yet.</p>
                                    </div>
                                @else
                                    <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                                        @foreach($event->users as $user)
                                            <div class="list-group-item px-0 border-0">
                                                <div class="d-flex align-items-center py-2">
                                                    <div class="flex-shrink-0">
                                                        <img src="{{ $user->profile_photo ? asset('storage/' . $user->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7f9cf5&background=EBF4FF' }}" alt="{{ $user->name }}" class="rounded-circle" width="40" height="40">
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <h6 class="mb-0">{{ $user->name }}</h6>
                                                        <small class="text-muted">{{ $user->pivot->attendance_status ?? 'pending' }}</small>
                                                    </div>
                                                    <div class="text-end">
                                                        <span class="badge {{ $user->pivot->attendance_status == 'confirmed' ? 'bg-success' : ($user->pivot->attendance_status == 'attended' ? 'bg-primary' : 'bg-secondary') }}">
                                                            {{ ucfirst($user->pivot->attendance_status ?? 'pending') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Form --}}
    <form id="deleteForm" action="{{ route('admin.event.destroy', $event) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    @push('js')
    <script>
        function confirmDelete() {
            if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
                document.getElementById('deleteForm').submit();
            }
        }
    </script>
    @endpush
</x-dashboard::layout>