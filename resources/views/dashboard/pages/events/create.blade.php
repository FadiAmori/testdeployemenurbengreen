<x-dashboard::layout bodyClass="bg-gray-200" titlePage="Create Event" activePage="events" :showSidebar="true">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-6 col-md-8 col-12 mx-auto">
                <div class="card">
                    <div class="card-header pb-0"><h6>Create New Event</h6></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.event.store') }}" enctype="multipart/form-data">
                            @csrf

                            <!-- Title -->
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" value="{{ old('title') }}">
                                @error('title')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description">{{ old('description') }}</textarea>
                                @error('description')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>

                            <!-- Event Date -->
                            <div class="mb-3">
                                <label class="form-label">Event Date</label>
                                <input type="datetime-local" class="form-control" name="event_date" value="{{ old('event_date') }}">
                                @error('event_date')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>

                            <!-- Location -->
                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" class="form-control" name="location" value="{{ old('location') }}">
                                @error('location')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>

                            <!-- Category -->
                            <div class="mb-3">
                                <label class="form-label">Category</label>
                                <select name="category_id" class="form-control">
                                    <option value="">Select a category</option>
                                    @foreach(\App\Models\Event\EventCategory::where('is_active', true)->get() as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>

                            <!-- Image -->
                            <div class="mb-3">
                                <label class="form-label">Image</label>
                                <input type="file" class="form-control" name="image">
                                @error('image')<p class="text-danger">{{ $message }}</p>@enderror
                            </div>

                            <!-- Publish Checkbox -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="is_published" id="is_published" {{ old('is_published') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">Publish Event</label>
                            </div>

                            <button type="submit" class="btn bg-gradient-success">Create Event</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-dashboard::layout>
