<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Rapport IA – {{ $report->period_start->toDateString() }} → {{ $report->period_end->toDateString() }}</title>
  <style>
    /* PDF-safe typography and layout */
    html, body { font-family: DejaVu Sans, Arial, sans-serif; color:#111; }
    body { margin: 16px; font-size: 13px; line-height: 1.45; }
    h1 { font-size: 22px; margin: 0 0 10px; }
    h2 { font-size: 16px; margin: 16px 0 8px; }
    h3 { font-size: 14px; margin: 14px 0 6px; }
    p { margin: 6px 0; }
    .meta { color:#666; font-size: 11px; }
    .badge { display:inline-block; border-radius: 999px; padding: 4px 8px; font-size: 11px; background:#f3f4f6; color:#111; }
    .ai { background:#e9e5ff; color:#312e81; }
    .card { border:1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin: 10px 0; }
    .grid { display: table; width:100%; table-layout: fixed; }
    .col { display: table-cell; vertical-align: top; }
    .gap { width: 16px; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #e5e7eb; padding: 6px; }
    th { background: #f8fafc; text-align:left; }
    .text-right { text-align: right; }
    .muted { color:#6b7280; }
    .delta-pos { color:#047857; font-weight: 600; }
    .delta-neg { color:#b91c1c; font-weight: 600; }
    .delta-neu { color:#6b7280; font-weight: 600; }
    .chip { display:inline-block; border-radius:6px; padding:2px 6px; font-size:11px; margin-right:4px; }
    .chip-p1 { background:#fde2e2; color:#7f1d1d; }
    .chip-p2 { background:#fef3c7; color:#7c2d12; }
    .chip-p3 { background:#e5e7eb; color:#111827; }
    .chip-done { background:#dcfce7; color:#065f46; }
    .chip-open { background:#e5e7eb; color:#111827; }
    .chip-progress { background:#dbeafe; color:#1e40af; }
    .avatar { display:inline-block; width:18px; height:18px; border-radius:50%; background:#e5e7eb; color:#111; font-weight:700; text-align:center; line-height:18px; font-size:10px; }
    .section-title { font-weight:700; font-size: 15px; margin: 10px 0 6px; }
    .small { font-size: 12px; }
  </style>
</head>
<body>
  {{-- Header --}}
  <div style="margin-bottom:8px;">
    <h1>Rapport IA – Période {{ $report->period_start->toDateString() }} → {{ $report->period_end->toDateString() }}</h1>
    <div class="meta">
      Généré le {{ optional($report->created_at)->format('d/m/Y H:i') }} · <span class="badge ai">AI</span>
    </div>
  </div>

  {{-- KPI Summary (if metrics exist) --}}
  @php
    $metrics = (array) ($metrics ?? []);
    $k = (array) ($metrics['kpis'] ?? []);
    $rows = [
      'Ventes' => (array) ($k['sales'] ?? []),
      'Commandes' => (array) ($k['orders'] ?? []),
      'AOV' => (array) ($k['aov'] ?? []),
      'Conversion' => (array) ($k['conversion_rate'] ?? []),
    ];
    $fmtEuro = fn ($n) => is_null($n) ? '—' : number_format((float)$n, 0, ',', ' ') . ' €';
    $fmtNum  = fn ($n) => is_null($n) ? '—' : number_format((float)$n, 0, ',', ' ');
  @endphp

  @if(!empty($metrics))
  <div class="card">
    <div class="section-title">Résumé KPI</div>
    <table>
      <thead><tr><th>KPI</th><th class="text-right">N</th><th class="text-right">N‑1</th><th class="text-right">Δ%</th></tr></thead>
      <tbody>
        @foreach($rows as $label => $row)
          @php
            $cur = $row['current'] ?? null; $prev = $row['previous'] ?? null; $dp = $row['delta_pct'] ?? null;
            $isMoney = in_array($label, ['Ventes','AOV']);
            $curTxt = $isMoney ? $fmtEuro($cur) : $fmtNum($cur);
            $prevTxt = $isMoney ? $fmtEuro($prev) : $fmtNum($prev);
            $deltaClass = is_null($dp) ? 'delta-neu' : ($dp > 0 ? 'delta-pos' : ($dp < 0 ? 'delta-neg' : 'delta-neu'));
            $deltaSymbol = is_null($dp) ? '•' : ($dp > 0 ? '↑' : ($dp < 0 ? '↓' : '•'));
          @endphp
          <tr>
            <td>{{ $label }}</td>
            <td class="text-right">{{ $curTxt }}</td>
            <td class="text-right muted">{{ $prevTxt }}</td>
            <td class="text-right {{ $deltaClass }}">{{ $deltaSymbol }} {{ is_null($dp) ? '—' : number_format($dp,1,',',' ') . ' %' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  {{-- Markdown content rendered below --}}
  <div class="card" style="page-break-inside: avoid;">
    {!! $markdownHtml !!}
  </div>

  {{-- Optional: recommendations table if present in metrics (future extension) --}}

</body>
</html>

