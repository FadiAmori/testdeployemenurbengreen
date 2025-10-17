{{-- Bootstrap 5.3 only fallback of the AI Report component --}}
@props(['report'])
@php
  $r = $report ?? [];
  $period = $r['period'] ?? ['start'=>'','end'=>''];
  $kpis = $r['kpis'] ?? [];
  $positives = $r['positives'] ?? [];
  $negatives = $r['negatives'] ?? [];
  $analysis = $r['analysis'] ?? [];
  $recs = $r['recommendations'] ?? [];
  $meta = $r['meta'] ?? [];
  $fmtNumber = fn ($n,$d=0)=>is_null($n)?'—':number_format((float)$n,$d,',',' ');
  $fmtEuro = fn ($n)=>is_null($n)?'—':number_format((float)$n,0,',',' ').' €';
  $deltaClass = fn($p)=> is_null($p)?'text-secondary':($p>0?'text-success':($p<0?'text-danger':'text-secondary'));
  $deltaIcon = fn($p)=> is_null($p)?'•':($p>0?'↑':($p<0?'↓':'•'));
  $sparkPoints = function (array $vals, $w=120, $h=30, $pad=2){ if(count($vals)<2) return ''; $min=min($vals); $max=max($vals); $span=max($max-$min,1e-9); $n=count($vals)-1; $pts=[]; foreach($vals as $i=>$v){$x=$pad+($w-2*$pad)*($i/$n); $y=$pad+($h-2*$pad)*(1-(($v-$min)/$span)); $pts[]=round($x,1).','.round($y,1);} return implode(' ',$pts); };
  $chip=function($label,$type='secondary'){ $map=['P1'=>'danger','P2'=>'warning','P3'=>'secondary','S'=>'success','M'=>'warning','L'=>'danger','Élevé'=>'danger','Moyen'=>'warning','Bas'=>'secondary','Open'=>'secondary','En cours'=>'info','Fait'=>'success']; $variant=$map[$type]??'secondary'; return "<span class=\"badge rounded-pill text-bg-$variant\">".e($label)."</span>"; };
@endphp

<section>
  <div class="d-flex align-items-center justify-content-between border-bottom pb-3">
    <div>
      <h1 class="h4 fw-bold mb-1">Rapport IA</h1>
      <div class="d-flex align-items-center gap-2">
        <span class="badge rounded-pill text-bg-light">{{ $period['start'] ?? '—' }} → {{ $period['end'] ?? '—' }}</span>
        <span class="badge rounded-pill text-bg-primary">AI</span>
        @if(!empty($meta['generated_at']))<small class="text-secondary">Généré le {{ $meta['generated_at'] }}</small>@endif
      </div>
    </div>
    @if(!empty($r['__pdf_url']))
      <div class="d-print-none">
        <a href="{{ $r['__pdf_url'] }}" class="btn btn-dark btn-sm">Export PDF</a>
      </div>
    @endif
  </div>

  <div class="row g-4 mt-3">
    <div class="col-lg-9">
      <div id="resume" class="card"><div class="card-body">
        <div class="d-flex align-items-center gap-2 mb-2"><span class="text-info"><i class="bi bi-info-circle-fill"></i></span><h2 class="h6 mb-0">Résumé exécutif (≤120 mots)</h2><span class="badge rounded-pill text-bg-primary">AI</span></div>
        <p class="mb-0">{{ $r['summary'] ?? '—' }}</p>
      </div></div>

      <div id="kpis" class="card mt-3"><div class="card-body">
        <h2 class="h6">KPI (table N vs N-1) + variation %</h2>
        <div class="table-responsive"><table class="table table-sm align-middle"><thead class="table-light"><tr><th>KPI</th><th class="text-end">N</th><th class="text-end">N‑1</th><th class="text-end">Δ%</th><th>Tendance</th></tr></thead><tbody>
          @foreach($kpis as $k)
            @php $name=$k['name']??'—'; $cur=$k['current']??null; $prev=$k['previous']??null; $dp=$k['delta_percent']??null; $spark=$k['sparkline']??[]; $isMoney=str_contains(mb_strtolower($name),'vente')||str_contains(mb_strtolower($name),'aov')||str_contains(mb_strtolower($name),'€'); $curTxt=$isMoney?$fmtEuro($cur):$fmtNumber($cur); $prevTxt=$isMoney?$fmtEuro($prev):$fmtNumber($prev); $pts=$sparkPoints($spark); @endphp
            <tr><th scope="row">{{ $name }}</th><td class="text-end fw-semibold">{{ $curTxt }}</td><td class="text-end text-secondary">{{ $prevTxt }}</td><td class="text-end {{ $deltaClass($dp) }}"><span>{{ $deltaIcon($dp) }}</span> {{ is_null($dp)?'—':$fmtNumber($dp,1).' %' }}</td><td>@if($pts!=='')<svg viewBox="0 0 120 30" width="110" height="28" role="img"><polyline fill="none" stroke="currentColor" stroke-width="2" points="{{ $pts }}" /></svg>@else<span class="text-secondary">—</span>@endif</td></tr>
          @endforeach
        </tbody></table></div>
      </div></div>

      <div class="row g-3 mt-1">
        <div id="positives" class="col-md-6"><div class="card border-success-subtle"><div class="card-body"><h2 class="h6 text-success d-flex align-items-center gap-2 mb-2"><i class="bi bi-check-circle-fill"></i> Ce qui fonctionne</h2><ul class="mb-0">@forelse($positives as $p)<li>{{ $p }}</li>@empty<li class="text-secondary">—</li>@endforelse</ul></div></div></div>
        <div id="negatives" class="col-md-6"><div class="card border-warning-subtle"><div class="card-body"><h2 class="h6 text-warning d-flex align-items-center gap-2 mb-2"><i class="bi bi-exclamation-triangle-fill"></i> Ce qui ne fonctionne pas</h2><ul class="mb-0">@forelse($negatives as $n)<li>{{ $n }}</li>@empty<li class="text-secondary">—</li>@endforelse</ul></div></div></div>
      </div>

      <div id="analysis" class="card border-start border-3 border-info mt-3"><div class="card-body"><h2 class="h6 mb-2">Analyse & causes probables</h2><ul class="mb-0">@forelse($analysis as $a)<li>{{ $a['text'] ?? '' }} @if(!empty($a['tags']))<span class="ms-2">@foreach($a['tags'] as $t)<span class="badge rounded-pill text-bg-light">{{ $t }}</span>@endforeach</span>@endif</li>@empty<li class="text-secondary">—</li>@endforelse</ul></div></div>

      <div id="recommendations" class="card mt-3"><div class="card-body"><div class="d-flex align-items-center justify-content-between"><h2 class="h6">Recommandations actionnables</h2><small class="text-secondary">Tri côté serveur</small></div>
        <div class="table-responsive d-none d-md-block mt-2"><table class="table table-sm align-middle"><thead class="table-light"><tr><th>Reco</th><th>Priorité</th><th>Effort</th><th>Impact</th><th>Propriétaire</th><th>Échéance</th><th>Statut</th></tr></thead><tbody>@forelse($recs as $rec)<tr><td>{{ $rec['text'] ?? '' }}</td><td>{!! $chip($rec['priority'] ?? 'P3', $rec['priority'] ?? 'P3') !!}</td><td>{!! $chip($rec['effort'] ?? 'M', $rec['effort'] ?? 'M') !!}</td><td>{!! $chip($rec['impact'] ?? 'Moyen', $rec['impact'] ?? 'Moyen') !!}</td><td>@php $ini=$rec['owner']['initials']??'??'; @endphp<span class="d-inline-flex align-items-center gap-2"><span class="rounded-circle bg-secondary-subtle d-inline-flex justify-content-center align-items-center" style="width:24px;height:24px;font-size:11px;font-weight:700;">{{ $ini }}</span><span>{{ $rec['owner']['name'] ?? '' }}</span></span></td><td>{{ $rec['due_date'] ?? '—' }}</td><td>{!! $chip($rec['status'] ?? 'Open', $rec['status'] ?? 'Open') !!}</td></tr>@empty<tr><td colspan="7" class="text-secondary">Aucune recommandation.</td></tr>@endforelse</tbody></table></div>
        <div class="d-md-none mt-2">@forelse($recs as $rec)<div class="card mb-2"><div class="card-body"><div class="fw-semibold mb-1">{{ $rec['text'] ?? '' }}</div><div class="d-flex flex-wrap gap-1 small">{!! $chip('Priorité: '.($rec['priority'] ?? 'P3'), $rec['priority'] ?? 'P3') !!} {!! $chip('Effort: '.($rec['effort'] ?? 'M'), $rec['effort'] ?? 'M') !!} {!! $chip('Impact: '.($rec['impact'] ?? 'Moyen'), $rec['impact'] ?? 'Moyen') !!} {!! $chip('Statut: '.($rec['status'] ?? 'Open'), $rec['status'] ?? 'Open') !!}</div><div class="d-flex align-items-center gap-2 small mt-2">@php $ini=$rec['owner']['initials']??'??'; @endphp<span class="rounded-circle bg-secondary-subtle d-inline-flex justify-content-center align-items-center" style="width:20px;height:20px;font-size:10px;font-weight:700;">{{ $ini }}</span><span>{{ $rec['owner']['name'] ?? '' }}</span><span class="ms-auto">Échéance: {{ $rec['due_date'] ?? '—' }}</span></div></div></div>@empty<div class="text-secondary">Aucune recommandation.</div>@endforelse</div>
      </div></div>
    </div>

    <aside class="col-lg-3 d-none d-lg-block"><nav class="sticky-top" style="top: 5rem;" aria-label="Sommaire"><div class="card"><div class="card-body"><h3 class="h6">Sommaire</h3><ol class="list-unstyled small mb-0"><li><a href="#resume" class="link-underline link-underline-opacity-0">Résumé exécutif</a></li><li><a href="#kpis" class="link-underline link-underline-opacity-0">KPI</a></li><li><a href="#positives" class="link-underline link-underline-opacity-0">Ce qui fonctionne</a></li><li><a href="#negatives" class="link-underline link-underline-opacity-0">Ce qui ne fonctionne pas</a></li><li><a href="#analysis" class="link-underline link-underline-opacity-0">Analyse</a></li><li><a href="#recommendations" class="link-underline link-underline-opacity-0">Recommandations</a></li></ol></div></div></nav></aside>
  </div>

  <style>@media print{.d-print-none{display:none!important}@page{margin:14mm}body::after{content:"URL: {{ url()->current() }}  –  Imprimé le: {{ now()->format('Y-m-d H:i') }}";position:fixed;bottom:8mm;left:14mm;right:14mm;font-size:10px;color:#666}}</style>
</section>
