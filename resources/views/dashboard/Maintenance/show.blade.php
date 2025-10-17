<x-dashboard::layout bodyClass="g-sidenav-show bg-gray-200">
    <x-dashboard::navbars.sidebar activePage="maintenance" />
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg" style="background-color: #000000; color: #ffffff;">
        <x-dashboard::navbars.navs.auth titlePage="View Maintenance" />
        <div class="container-fluid py-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="color: #000000;">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert" style="color: #000000;">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="row">
                <div class="col-12">
                    <div class="card" style="border: none; box-shadow: 0 6px 12px rgba(0, 0, 0, 0.5); border-radius: 12px; overflow: hidden; background-color: #1a1a1a;">
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <h1 style="font-size: 2.5rem; color: #FF6347; font-weight: 700; margin-bottom: 12px; line-height: 1.2;">{{ $maintenance->product->name }}</h1>
                                <div style="font-size: 1.1rem; color: #cccccc; display: flex; align-items: center; gap: 10px;">
                                    <span>Category: </span>
                                    <span style="background-color: #FFE4B5; padding: 4px 12px; border-radius: 16px; font-size: 0.9rem; color: #2F4F4F;">{{ $maintenance->product->category->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="row g-4 mb-4">
                                <div class="col-lg-8">
                                    @if ($maintenance->video)
                                        <video controls style="width: 100%; height: 400px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);">
                                            <source src="{{ Storage::url($maintenance->video) }}" type="video/mp4">
                                            <source src="{{ Storage::url($maintenance->video) }}" type="video/webm">
                                            <p>Your browser does not support the video tag. <a href="{{ Storage::url($maintenance->video) }}">Download the video</a>.</p>
                                        </video>
                                    @elseif ($maintenance->photo)
                                        <img src="{{ Storage::url($maintenance->photo) }}" alt="Maintenance Image" style="width: 100%; height: 400px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);" onerror="this.src='/placeholder-image.jpg'; this.onerror=null;">
                                    @else
                                        <img src="/placeholder-image.jpg" alt="Placeholder Image" style="width: 100%; height: 400px; object-fit: cover; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);">
                                    @endif
                                </div>
                                <div class="col-lg-4">
                                    <div class="row g-2 h-100">
                                        @if ($maintenance->photo)
                                            <div class="col-12 col-md-12">
                                                <img src="{{ Storage::url($maintenance->photo) }}" alt="Thumbnail 1" class="thumbnail-img" onerror="this.src='/placeholder-image.jpg'; this.onerror=null;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row g-4">
                                <div class="col-lg-4">
                                    <div style="padding: 20px; background-color: #2c2c2c; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);">
                                        <h3 style="color: #FF6347; border-bottom: 2px solid #FF6347; padding-bottom: 8px; margin-bottom: 16px; font-size: 1.5rem; font-weight: 600;">Description</h3>
                                        <p style="line-height: 1.6; color: #cccccc;">{{ $maintenance->description ?? 'No description available.' }}</p>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div style="padding: 20px; background-color: #2c2c2c; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);">
                                        <h3 style="color: #FF6347; border-bottom: 2px solid #FF6347; padding-bottom: 8px; margin-bottom: 16px; font-size: 1.5rem; font-weight: 600;">Required Materials</h3>
                                        <div class="materials-grid">
                                            @if ($maintenance->material)
                                                <div class="material-item">
                                                    <img src="{{ $maintenance->material->primary_image_url ?? '/placeholder-image.jpg' }}" alt="{{ $maintenance->material->name }} Image" style="width: 64px; height: 64px; object-fit: cover; border-radius: 50%; margin-bottom: 8px; border: 2px solid #FFE4B5;" onerror="this.src='/placeholder-image.jpg'; this.onerror=null;">
                                                    <div style="font-weight: 600; text-align: center; margin-bottom: 4px; color: #ffffff;">{{ $maintenance->material->name }}</div>
                                                </div>
                                            @else
                                                <p style="color: #cccccc; text-align: center;">No required materials.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div style="padding: 20px; background-color: #2c2c2c; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);">
                                        <h3 style="color: #FF6347; border-bottom: 2px solid #FF6347; padding-bottom: 8px; margin-bottom: 16px; font-size: 1.5rem; font-weight: 600;">Optional Products</h3>
                                        <div class="materials-grid">
                                            @if ($maintenance->optional)
                                                <div class="material-item">
                                                    <img src="{{ $maintenance->optional->primary_image_url ?? '/placeholder-image.jpg' }}" alt="{{ $maintenance->optional->name }} Image" style="width: 64px; height: 64px; object-fit: cover; border-radius: 50%; margin-bottom: 8px; border: 2px solid #FFE4B5;" onerror="this.src='/placeholder-image.jpg'; this.onerror=null;">
                                                    <div style="font-weight: 600; text-align: center; margin-bottom: 4px; color: #ffffff;">{{ $maintenance->optional->name }}</div>
                                                </div>
                                            @else
                                                <p style="color: #cccccc; text-align: center;">No optional products.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if ($maintenance->steps && count($maintenance->steps) > 0)
                                <div style="margin-top: 24px; background-color: #2c2c2c; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);">
                                    <h3 style="color: #FF6347; border-bottom: 2px solid #FF6347; padding-bottom: 8px; margin-bottom: 16px; font-size: 1.5rem; font-weight: 600;">Steps</h3>
                                    @foreach ($maintenance->steps as $index => $step)
                                        <div style="display: flex; gap: 16px; margin-bottom: 24px; padding-left: 40px; position: relative;">
                                            <div style="position: absolute; left: 0; top: 0; width: 32px; height: 32px; background-color: #FF6347; color: #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">{{ $index + 1 }}</div>
                                            <div>
                                                <h4 style="margin: 0 0 8px 0; color: #FF6347; font-weight: 600;">{{ $step['title'] ?? 'Step ' . ($index + 1) }}</h4>
                                                <p style="margin: 0; line-height: 1.6; color: #cccccc;">{{ $step['description'] ?? 'No description provided.' }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div style="display: flex; justify-content: space-between; padding: 20px 32px; background-color: #2F4F4F; border-top: 1px solid rgba(255, 255, 255, 0.1);">
                            <a href="{{ route('maintenance.category', ['categoryId' => $maintenance->product->category_id]) }}" class="btn custom-back-btn">
                                ← Back to Category
                            </a>
                            <div>
                                <a href="{{ route('maintenance.edit', $maintenance->product_id) }}" class="btn custom-edit-btn" aria-label="Edit maintenance for {{ $maintenance->product->name }}">
                                    Edit Maintenance
                                </a>
                                <a href="{{ route('maintenance.pdf', $maintenance->product_id) }}" class="btn btn-dark" style="margin-right:8px;">Download PDF</a>
                                <form action="{{ route('maintenance.destroy', $maintenance->product_id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger custom-delete-btn" onclick="return confirm('Are you sure you want to delete this maintenance record?')">
                                        Delete Maintenance
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-dashboard::layout>

<style>
    .thumbnail-img {
        width: 100%;
        height: 240px;
        object-fit: cover;
        border-radius: 8px;
        border: 3px solid #FF6347;
        cursor: pointer;
        opacity: 1;
        transition: all 0.3s ease;
        background-color: #333;
    }
    .thumbnail-img:hover {
        opacity: 1;
        transform: scale(1.02);
    }
    .materials-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 16px;
        padding: 16px;
        background-color: #333;
        border-radius: 8px;
    }
    .material-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 12px;
        background-color: #1a1a1a;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }
    .material-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
    }
    .custom-back-btn {
        text-decoration: none;
        border-radius: 20px;
        font-weight: 500;
        padding: 10px 20px;
        transition: all 0.3s ease;
        color: #2F4F4F;
        background-color: #4a4a4a;
    }
    .custom-back-btn:hover {
        transform: translateX(-5px);
        background-color: #666;
    }
    .custom-edit-btn {
        text-decoration: none;
        margin-right: 10px;
        border-radius: 20px;
        font-weight: 500;
        padding: 10px 20px;
        background-color: #FF6347;
        border-color: #FF6347;
        color: #ffffff;
        transition: all 0.3s ease;
        display: inline-block;
    }
    .custom-edit-btn:hover {
        opacity: 0.9;
        transform: scale(1.05);
    }
    .custom-edit-btn:focus {
        outline: 2px solid #FFE4B5;
        outline-offset: 2px;
    }
    .custom-delete-btn {
        border-radius: 20px;
        font-weight: 500;
        padding: 10px 20px;
        transition: all 0.3s ease;
    }
    .custom-delete-btn:hover {
        transform: translateX(5px);
    }
    .custom-delete-btn:focus {
        outline: 2px solid #FFE4B5;
        outline-offset: 2px;
    }
</style>