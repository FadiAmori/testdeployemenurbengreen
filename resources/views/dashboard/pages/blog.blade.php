
<x-dashboard::layout bodyClass="g-sidenav-show bg-gray-200">
    <x-dashboard::navbars.sidebar activePage="blog" />
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
        <x-dashboard::navbars.navs.auth titlePage="Blog" />

        <div class="container-fluid py-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Statutes</h5>
                    <!-- Search Form -->
                    <form action="{{ route('admin.blog') }}" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control form-control-sm me-2" 
                               placeholder="Search statutes..." 
                               value="{{ request('search') }}" 
                               style="max-width: 300px;">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search"></i> Search
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.blog') }}" class="btn btn-sm btn-secondary ms-2">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        @endif
                    </form>
                </div>
                <div class="card-body">
                    <!-- Overview Chart Section -->
                    <div class="mb-4" style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                        <h6 class="mb-3">📊 Overview: Comments per Statute</h6>
                        
                        <!-- Debug Info -->
                        <div class="mb-3 small text-muted">
                            <strong>Data Check:</strong> 
                            Statutes: {{ count($labels ?? []) }}, 
                            Total Comments: {{ array_sum($commentsData ?? []) }}
                        </div>
                        
                        <div style="max-width: 700px; height: 400px; margin: 0 auto; position: relative;">
                            <canvas id="overviewDonut" data-ids='{!! json_encode($ids ?? []) !!}' data-labels='{!! json_encode($labels ?? []) !!}' data-comments='{!! json_encode($commentsData ?? []) !!}'></canvas>
                        </div>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($statutes as $statute)
                                <tr>
                                    <td>
                                        @if($statute->photo)
                                            <img src="{{ asset($statute->photo) }}" alt="{{ $statute->titre }}" width="80" height="80" style="object-fit:cover; border-radius:5px;">
                                        @else
                                            <span class="text-muted">No Photo</span>
                                        @endif
                                    </td>
                                    <td>{{ $statute->titre }}</td>
                                    <td>{{ $statute->description }}</td>
                               
                                    <td>
                                        <!-- View Comments Button -->
                                        <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#commentsModal{{ $statute->id }}">
                                            View Comments
                                        </button>

                                        <!-- Reacte Button -->
                                        <button class="btn btn-primary btn-sm ms-1" data-bs-toggle="modal" data-bs-target="#reactionsModal{{ $statute->id }}">
                                            Reacte
                                        </button>

                                        <!-- Delete Statute Form -->
                                        <form action="{{ route('statutes.destroy', $statute) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Comments Modal -->
                                <div class="modal fade" id="commentsModal{{ $statute->id }}" tabindex="-1" aria-labelledby="commentsModalLabel{{ $statute->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="commentsModalLabel{{ $statute->id }}">Comments for "{{ $statute->titre }}"</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                @forelse($statute->comentes as $comente)
                                                    <div class="d-flex justify-content-between mb-2">
                                                        <span>{{ $comente->description }}</span>
                                                        <form action="{{ route('comentes.destroy', $comente) }}" method="POST" style="display:inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this comment?')">Delete</button>
                                                        </form>
                                                    </div>
                                                @empty
                                                    <p>No comments yet.</p>
                                                @endforelse
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Reactions Modal -->
                                <div class="modal fade" id="reactionsModal{{ $statute->id }}" tabindex="-1" aria-labelledby="reactionsModalLabel{{ $statute->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="reactionsModalLabel{{ $statute->id }}">Reactions for "{{ $statute->titre }}"</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                @if($statute->reactions->isEmpty())
                                                    <p>No reactions yet.</p>
                                                @else
                                                    <ul class="list-group">
                                                        @foreach($statute->reactions as $reaction)
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <strong>{{ $reaction->user->name ?? 'User #' . $reaction->user_id }}</strong>
                                                                    <div class="text-muted small">{{ $reaction->created_at->diffForHumans() }}</div>
                                                                </div>
                                                                <span class="badge bg-secondary">{{ ucfirst($reaction->reaction) }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $statutes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Chart.js CDN - Latest stable version -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js"></script>
    <script>
    <!-- Chart.js CDN - Latest stable version -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.js" crossorigin="anonymous"></script>
    <script>
        (function() {
            console.log('Script loading...');
            
            // Function to initialize charts
            function initializeCharts() {
                console.log('initializeCharts called, Chart available:', typeof Chart);
                
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js not loaded yet, retrying in 500ms...');
                    setTimeout(initializeCharts, 500);
                    return;
                }
            // Initialize comment donuts per statute
            document.querySelectorAll('canvas[id^="commentsDonut"]').forEach(function (canvas) {
                const comments = parseInt(canvas.dataset.comments, 10) || 0;
                const max = parseInt(canvas.dataset.max, 10) || 1;
                const remaining = Math.max(0, max - comments);

                new Chart(canvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        datasets: [{
                            data: [comments, remaining],
                            backgroundColor: ['#4caf50', '#e0e0e0'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: false,
                        cutout: '70%',
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: true }
                        }
                    }
                });
            });

            // Overview donut chart (comments distribution)
            const overviewCanvas = document.getElementById('overviewDonut');
            let overviewChart = null;
            
            console.log('=== OVERVIEW CHART INITIALIZATION ===');
            console.log('1. Chart.js available:', typeof Chart !== 'undefined');
            console.log('2. Canvas found:', !!overviewCanvas);
            
            if (!overviewCanvas) {
                console.error('❌ Canvas element not found!');
                return;
            }
            
            if (typeof Chart === 'undefined') {
                console.error('❌ Chart.js not loaded!');
                return;
            }
            
            try {
                const labelsRaw = overviewCanvas.dataset.labels;
                const commentsRaw = overviewCanvas.dataset.comments;
                console.log('3. Raw data:', {labels: labelsRaw, comments: commentsRaw});
                
                const labels = JSON.parse(labelsRaw || '[]');
                const comments = JSON.parse(commentsRaw || '[]');
                console.log('4. Parsed data:', {labels, comments});
                
                if (labels.length === 0 || comments.length === 0) {
                    console.warn('⚠️ No data to display');
                    overviewCanvas.parentElement.innerHTML = '<div class="alert alert-info">No comments data available yet</div>';
                    return;
                }
                
                // Color palette
                const colors = ['#4caf50','#2196f3','#ff9800','#9c27b0','#f44336','#03a9f4','#8bc34a','#ffc107'];
                const bg = labels.map((_, i) => colors[i % colors.length]);

                console.log('5. Creating chart...');
                overviewChart = new Chart(overviewCanvas.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: comments,
                            backgroundColor: bg,
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
                                text: 'Comments Distribution',
                                font: { size: 16 }
                            },
                            tooltip: {
                                enabled: true,
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a,b)=>a+(+b),0) || 1;
                                        const value = context.parsed || 0;
                                        const pct = ((value / total) * 100).toFixed(1);
                                        return context.label + ': ' + value + ' comments (' + pct + '%)';
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
                                            text: label + ' (' + data.datasets[0].data[i] + ')',
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
                console.log('✅ Chart created successfully!', overviewChart);
            } catch (e) {
                console.error('❌ Error creating chart:', e.message);
                console.error('Stack:', e.stack);
                overviewCanvas.parentElement.innerHTML = '<div class="alert alert-danger">Error loading chart: ' + e.message + '</div>';
            }

            // Initialize reaction pie when modal opens
            document.querySelectorAll('[id^="reactionsModal"]').forEach(function(modalEl) {
                modalEl.addEventListener('show.bs.modal', function (event) {
                    const modal = event.currentTarget;
                    const canvasId = 'reactionsPie' + modal.id.replace('reactionsModal', '');
                    let canvas = modal.querySelector('#' + canvasId);
                    if (!canvas) {
                        // create canvas inside modal body
                        const body = modal.querySelector('.modal-body');
                        canvas = document.createElement('canvas');
                        canvas.id = canvasId;
                        canvas.width = 200;
                        canvas.height = 200;
                        body.insertBefore(canvas, body.firstChild);
                    }

                    // Read counts from DOM (badge text) inside modal list
                    const likes = Array.from(modal.querySelectorAll('.badge'))
                        .filter(b => /like/i.test(b.textContent))
                        .length;
                    const dislikes = Array.from(modal.querySelectorAll('.badge'))
                        .filter(b => /dislike/i.test(b.textContent))
                        .length;

                    // destroy previous chart if exists
                    if (canvas._chartInstance) {
                        canvas._chartInstance.destroy();
                    }

                    canvas._chartInstance = new Chart(canvas.getContext('2d'), {
                        type: 'pie',
                        data: {
                            labels: ['Likes', 'Dislikes'],
                            datasets: [{
                                data: [likes, dislikes],
                                backgroundColor: ['#4caf50', '#f44336']
                            }]
                        },
                        options: {
                            responsive: false,
                            plugins: { legend: { position: 'bottom' } }
                        }
                    });
                });
            });
            }
            
            // Try to initialize immediately
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initializeCharts);
            } else {
                // DOM already loaded
                initializeCharts();
            }
        })();
    </script>
</x-dashboard::layout>
