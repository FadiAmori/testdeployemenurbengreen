@php use Illuminate\Support\Str; @endphp

<x-dashboard::layout bodyClass="g-sidenav-show bg-gray-200">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-dashboard::navbars.sidebar activePage="ai-report"></x-dashboard::navbars.sidebar>
    <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg" x-data="adminAiReport()" x-cloak>
        <x-dashboard::navbars.navs.auth titlePage="Rapport IA"></x-dashboard::navbars.navs.auth>
        <div class="container-fluid py-4">
            <div class="row g-4">
                <!-- History column (toggle) -->
                <div class="col-12 col-lg-4" x-show="showHistory" x-transition>
                    <div class="card shadow-sm h-100">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h6 class="mb-0">Historique des rapports</h6>
                            <div class="btn-group" role="group">
                                <button class="btn btn-outline-secondary btn-sm" @click="showHistory=false" type="button">Masquer</button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info" x-show="loading">
                                <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                Génération en cours…
                            </div>
                            <div class="alert alert-danger" x-show="error" x-text="error" role="alert"></div>
                            <div class="report-cards">
                                @foreach(($reports ?? collect()) as $r)
                                    <a class="report-card" href="{{ route('admin.ai-report.index', ['id' => $r->id]) }}" data-report-id="{{ $r->id }}">
                                        <div class="report-card__header">
                                            <span class="report-card__period">{{ $r->period_start->toDateString() }} → {{ $r->period_end->toDateString() }}</span>
                                            <span class="report-card__date">{{ $r->created_at?->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <div class="report-card__body">
                                            <p>{{ \Illuminate\Support\Str::limit($r->markdown, 140) }}</p>
                                        </div>
                                        <div class="report-card__actions">
                                            <span class="badge bg-success">Voir</span>
                                            <span class="badge bg-secondary" onclick="event.preventDefault();window.location='{{ route('admin.ai-report.pdf', $r->id) }}'">PDF</span>
                                            <span class="badge bg-danger" role="button" onclick="event.preventDefault();deleteReport({{ $r->id }});">Supprimer</span>
                                        </div>
                                    </a>
                                @endforeach
                                @if(($reports ?? collect())->isEmpty())
                                    <p class="text-sm text-muted mb-0">Aucun rapport généré pour l’instant.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Detail column grows to full width when history hidden -->
                <div :class="showHistory ? 'col-12 col-lg-8' : 'col-12'">
                    <div class="card shadow-sm h-100">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h5 class="mb-0">Détail du rapport</h5>
                                <small class="text-muted">Aperçu du rapport sélectionné</small>
                            </div>
                            <div>
                                <button class="btn btn-success btn-sm me-2" @click="generateNow" :disabled="loading">
                                    <i class="material-icons me-1">play_circle</i> Générer
                                </button>
                                <button class="btn btn-outline-secondary btn-sm me-2" @click="showHistory=true" type="button" x-show="!showHistory">Historique</button>
                                <button class="btn btn-outline-secondary btn-sm" @click="downloadPdf" :disabled="!report?.id">
                                    <i class="material-icons me-1">picture_as_pdf</i> PDF
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Render the polished Bootstrap component built from metrics --}}
                            @if(!empty($uiReport))
                                <x-ai-report-bootstrap :report="$uiReport" />
                            @else
                                <p class="text-sm text-muted">Sélectionnez un rapport dans la liste à gauche.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('adminAiReport', () => ({
                report: @json($latestReport),
                reportHtml: @json(($latestReport ?? null) ? Str::markdown($latestReport->markdown) : null),
                loading: false,
                error: null,
                showHistory: false,
                charts: { },
                aiBaseUrl: @json($aiBaseUrl ?? ''),
                async generateNow() {
                    this.loading = true; this.error = null;
                    try {
                        const res = await fetch('{{ url('/admin/ai-report/generate') }}', {
                            method: 'POST',
                            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
                            body: JSON.stringify({})
                        });
                        if (!res.ok) { const data = await res.json().catch(()=>({message:'Erreur'})); this.error = data.message || 'Erreur génération'; return; }
                        await Promise.all([this.refreshLatest(), this.refreshList()]);
                    } catch (e) { console.error(e); this.error = 'Erreur lors de la génération.'; }
                    finally { this.loading = false; }
                },
                async refreshLatest() {
                    const res = await fetch('{{ url('/admin/ai-report/latest') }}', { headers: { 'Accept': 'application/json' } });
                    if (res.ok) {
                        const data = await res.json();
                        this.report = data;
                        this.reportHtml = window.marked ? window.marked.parse(data.markdown) : data.markdown;
                        // Page will be reloaded on list refresh to rebuild UI component
                    }
                },
                async refreshList() {
                    // Keep page state to allow devtools inspection; just open history panel
                    this.showHistory = true;
                },
                downloadPdf() {
                    if (!this.report?.id) return; window.location.href = `{{ url('/admin/ai-report') }}/${this.report.id}/pdf`;
                },
                // charts removed in favor of the reusable component output
            }));
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js" crossorigin="anonymous"></script>
    <script>
      async function deleteReport(id){
        if(!confirm('Supprimer ce rapport ?')) return;
        try{
          const res = await fetch(`{{ url('/admin/ai-report') }}/${id}`, {
            method: 'DELETE',
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
          });
          if (res.ok) {
            const el = document.querySelector(`[data-report-id="${id}"]`);
            if (el) el.remove();
            // If the deleted report is currently selected, refresh latest without reloading
            if (Alpine.store && Alpine.store('ai') && Alpine.store('ai').report?.id === id) {
              await Alpine.store('ai').refreshLatest?.();
            }
          }
        }catch(e){ console.error(e); }
      }
    </script>
    @endpush

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        .markdown-body h1 { font-size: 1.4rem; margin-top: 1rem; font-weight: 700; }
        .markdown-body h2 { font-size: 1.1rem; margin-top: .75rem; font-weight: 600; }
        .markdown-body table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        .markdown-body table td, .markdown-body table th { border: 1px solid #e2e8f0; padding: .5rem; }
        .markdown-body ul { padding-left: 1.25rem; }
        .report-cards { display: grid; gap: .75rem; }
        .report-card { display: block; text-decoration: none; color: inherit; border: 1px solid #e2e8f0; border-radius: 10px; padding: .75rem; transition: box-shadow .15s, transform .05s; background: #fff; }
        .report-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.06); transform: translateY(-1px); }
        .report-card__header { display: flex; justify-content: space-between; gap: .5rem; font-size: .85rem; color: #64748b; margin-bottom: .25rem; }
        .report-card__period { font-weight: 600; color: #0f172a; }
        .report-card__actions { display: flex; gap: .5rem; margin-top: .5rem; }
        .kpi-cards { display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap: .75rem; margin-bottom: 1rem; }
        .kpi-card { border:1px solid #e2e8f0; border-radius:10px; padding:.75rem; background:#fff; }
        .kpi-card__label { color:#64748b; font-size:.85rem; }
        .kpi-card__value { font-weight:700; font-size:1.25rem; }
    </style>
    @endpush
</x-dashboard::layout>
