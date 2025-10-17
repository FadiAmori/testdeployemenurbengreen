php
<x-dashboard::layout bodyClass="g-sidenav-show bg-gray-200">
    <x-dashboard::navbars.sidebar activePage="maintenance" />
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-dashboard::navbars.navs.auth titlePage="Maintenance - Products in {{ $category->name }}" />
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

            <!-- Statistics Section -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">📊 Product Favorites Statistics</h5>
                        </div>
                        <div class="card-body" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            <!-- Debug Info -->
                            <div class="mb-3 small text-muted">
                                <strong>Data Check:</strong> 
                                Products: {{ $category->products->count() }}, 
                                Total Favorites: {{ $category->products->sum('favorited_by_users_count') }}
                            </div>
                            
                            @if($category->products->count() > 0)
                                @php
                                    $productNames = [];
                                    $favoriteCounts = [];
                                    foreach($category->products as $product) {
                                        $productNames[] = $product->name;
                                        $favoriteCounts[] = $product->favorited_by_users_count ?? 0;
                                    }
                                    $totalFavorites = array_sum($favoriteCounts);
                                @endphp
                                
                                @if($totalFavorites > 0)
                                    <div style="max-width: 700px; height: 400px; margin: 0 auto; position: relative;">
                                        <canvas id="favoritesDonut" 
                                                data-labels='{!! json_encode($productNames) !!}' 
                                                data-favorites='{!! json_encode($favoriteCounts) !!}'>
                                        </canvas>
                                    </div>
                                @else
                                    <div class="alert alert-info text-center">
                                        <i class="material-icons" style="font-size: 48px;">favorite_border</i>
                                        <p class="mb-0 mt-2">No favorites yet. Products need to be favorited by users to see statistics.</p>
                                    </div>
                                @endif
                            @else
                                <div class="text-center text-muted py-5">
                                    <p>No products available to display statistics.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Category Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Total Products:</span>
                                <strong>{{ $category->products->count() }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">Total Favorites:</span>
                                <strong>{{ $category->products->sum('favorited_by_users_count') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="text-muted">With Maintenance:</span>
                                <strong>{{ $category->products->filter(fn($p) => $p->maintenance)->count() }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Most Favorited:</span>
                                <strong>
                                    @php
                                        $mostFavorited = $category->products->sortByDesc('favorited_by_users_count')->first();
                                    @endphp
                                    {{ $mostFavorited ? $mostFavorited->favorited_by_users_count : 0 }} ♥
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Material Filter Form -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <form method="GET" action="{{ route('maintenance.category', ['categoryId' => $category->id]) }}">
                        <div class="input-group">
                            <input type="text" name="material" class="form-control" placeholder="Filter by material..." value="{{ request()->query('material') }}">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-8 text-end">
                    <a href="{{ route('admin.maintenance.categories') }}" class="btn btn-secondary">Back to Categories</a>
                </div>
            </div>

            <!-- Products List -->
            <div class="row">
                @forelse ($category->products as $product)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <img src="{{ $product->primary_image_url ?? asset('urbangreen/img/bg-img/9.jpg') }}" alt="{{ $product->name }}" class="card-img-top img-fluid" style="max-height: 200px; object-fit: cover;">
                                <h5 class="card-title mt-3">{{ $product->name }}</h5>
                                <p class="text-muted mb-2">
                                    @if ($product->trashed())
                                        <span class="badge bg-danger">Soft-Deleted</span>
                                    @endif
                                    <span class="badge bg-info ms-2">
                                        <i class="material-icons" style="font-size: 14px; vertical-align: middle;">favorite</i>
                                        {{ $product->favorited_by_users_count }} Favorites
                                    </span>
                                </p>
                                @if ($product->maintenance)
                                    <a href="{{ route('maintenance.show', $product->id) }}" class="btn btn-primary btn-sm">View Maintenance</a>
                                    <a href="{{ route('maintenance.edit', $product->id) }}" class="btn btn-warning btn-sm">Edit Maintenance</a>
                                    <a href="{{ route('maintenance.pdf', $product->id) }}" class="btn btn-dark btn-sm">Download PDF</a>
                                @else
                                    <a href="{{ route('maintenance.create') }}?product_id={{ $product->id }}" class="btn btn-success btn-sm">Create Maintenance</a>
                                @endif
                                <a href="{{ route('maintenance.product.notifications', $product->id) }}" class="btn btn-info btn-sm mt-1">
                                    <i class="material-icons">notifications</i> View Notif
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <p class="text-muted mb-0">No products found for this category.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
        
        <!-- Chart.js CDN - Latest stable version -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js" crossorigin="anonymous"></script>
        <script>
        (function() {
            console.log('Favorites Chart Script loading...');
            
            // Function to initialize chart
            function initializeFavoritesChart() {
                console.log('initializeFavoritesChart called, Chart available:', typeof Chart);
                
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js not loaded yet, retrying in 500ms...');
                    setTimeout(initializeFavoritesChart, 500);
                    return;
                }

                const favoritesCanvas = document.getElementById('favoritesDonut');
                
                console.log('=== FAVORITES CHART INITIALIZATION ===');
                console.log('1. Chart.js available:', typeof Chart !== 'undefined');
                console.log('2. Canvas found:', !!favoritesCanvas);
                
                if (!favoritesCanvas) {
                    console.error('❌ Canvas element not found!');
                    return;
                }
                
                try {
                    const labelsRaw = favoritesCanvas.dataset.labels;
                    const favoritesRaw = favoritesCanvas.dataset.favorites;
                    console.log('3. Raw data:', {labels: labelsRaw, favorites: favoritesRaw});
                    
                    const labels = JSON.parse(labelsRaw || '[]');
                    const favorites = JSON.parse(favoritesRaw || '[]').map(f => parseInt(f) || 0);
                    console.log('4. Parsed data:', {labels, favorites});
                    console.log('4b. Labels length:', labels.length, 'Favorites length:', favorites.length);
                    console.log('4c. Total favorites:', favorites.reduce((a,b) => a+b, 0));
                    
                    if (labels.length === 0) {
                        console.warn('⚠️ No products to display');
                        favoritesCanvas.parentElement.innerHTML = '<div class="alert alert-info">No products available</div>';
                        return;
                    }
                    
                    const totalFavs = favorites.reduce((a,b) => a+b, 0);
                    if (totalFavs === 0) {
                        console.warn('⚠️ No favorites yet');
                        favoritesCanvas.parentElement.innerHTML = '<div class="alert alert-info text-center"><i class="material-icons" style="font-size:48px;">favorite_border</i><p class="mt-2">No favorites yet. Add some products to favorites!</p></div>';
                        return;
                    }
                    
                    // Color palette - pink/red theme for favorites
                    const colors = ['#e91e63', '#f06292', '#ec407a', '#ad1457', '#c2185b', '#d81b60', '#f48fb1', '#f50057'];
                    const backgroundColor = labels.map((_, i) => colors[i % colors.length]);

                    console.log('5. Creating donut chart...');
                    const favoritesChart = new Chart(favoritesCanvas.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: favorites,
                                backgroundColor: backgroundColor,
                                borderWidth: 2,
                                borderColor: '#fff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '60%',
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Favorites Distribution by Product',
                                    font: { size: 16, weight: 'bold' },
                                    color: '#333'
                                },
                                tooltip: {
                                    enabled: true,
                                    callbacks: {
                                        label: function(context) {
                                            const total = context.dataset.data.reduce((a, b) => a + (+b), 0) || 1;
                                            const value = context.parsed || 0;
                                            const pct = ((value / total) * 100).toFixed(1);
                                            return context.label + ': ' + value + ' favorite(s) (' + pct + '%)';
                                        }
                                    }
                                },
                                legend: { 
                                    position: 'right',
                                    display: true,
                                    labels: {
                                        font: { size: 13 },
                                        padding: 15,
                                        generateLabels: function(chart) {
                                            const data = chart.data;
                                            return data.labels.map((label, i) => ({
                                                text: label + ' (' + data.datasets[0].data[i] + ' ♥)',
                                                fillStyle: data.datasets[0].backgroundColor[i],
                                                hidden: false,
                                                index: i
                                            }));
                                        }
                                    }
                                }
                            }
                        }
                    });
                    console.log('✅ Favorites chart created successfully!', favoritesChart);
                } catch (e) {
                    console.error('❌ Error creating chart:', e.message);
                    console.error('Stack:', e.stack);
                    favoritesCanvas.parentElement.innerHTML = '<div class="alert alert-danger">Error loading chart: ' + e.message + '</div>';
                }
            }
            
            // Try to initialize immediately
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeFavoritesChart);
            } else {
                // DOM already loaded
                initializeFavoritesChart();
            }
        })();
        </script>
    </main>
</x-dashboard::layout>