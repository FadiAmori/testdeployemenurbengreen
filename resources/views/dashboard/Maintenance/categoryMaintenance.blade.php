<x-dashboard::layout bodyClass="g-sidenav-show bg-gray-200">
    <x-dashboard::navbars.sidebar activePage="maintenance" />
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-dashboard::navbars.navs.auth titlePage="Maintenance - Categories" />
        <div class="container-fluid py-4">
            <!-- Success Message -->
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <!-- Warning Message -->
            @if (isset($message))
                <div class="alert alert-warning">
                    {{ $message }}
                </div>
            @endif
            <!-- Material Filter Form -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <form method="GET" action="{{ route('admin.maintenance.categories') }}">
                        <div class="input-group">
                            <input type="text" name="material" class="form-control" placeholder="Filter by material..." value="{{ request()->query('material') }}">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Categories List -->
            <div class="row">
                @forelse ($categories as $category)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100" onclick="window.location='{{ route('maintenance.category', ['categoryId' => $category->id]) }}'" style="cursor: pointer;">
                            <div class="card-body text-center">
                                <img src="{{ $category->image_url ?? asset('urbangreen/img/bg-img/9.jpg') }}" alt="{{ $category->name }}" class="card-img-top img-fluid" style="max-height: 200px; object-fit: cover;">
                                <h5 class="card-title mt-3">{{ $category->name }}</h5>
                                <p class="text-muted">
                                    {{ $category->products->count() }} Product{{ $category->products->count() === 1 ? '' : 's' }}
                                    @if ($category->products->whereNotNull('deleted_at')->count() > 0)
                                        ({{ $category->products->whereNotNull('deleted_at')->count() }} soft-deleted)
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <p class="text-muted mb-0">No categories found.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </main>
</x-dashboard::layout>