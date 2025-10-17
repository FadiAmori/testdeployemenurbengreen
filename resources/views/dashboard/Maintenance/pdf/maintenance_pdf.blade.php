<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Maintenance - {{ $maintenance->product->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .product-info { margin-bottom: 15px; }
        .product-info h2 { margin: 0 0 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #f2f2f2; }
        .valide { height: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Maintenance Sheet</h1>
        <p>Product maintenance detail</p>
    </div>

    <div class="product-info">
        <h2>{{ $maintenance->product->name }}</h2>
        <p><strong>Category:</strong> {{ optional($maintenance->product->category)->name ?? '-' }}</p>
        <p><strong>Description:</strong></p>
        <div>{{ $maintenance->description ?? '-' }}</div>
    </div>

    <h3>Steps</h3>
    <table>
        <thead>
            <tr>
                <th style="width:10%">#</th>
                <th style="width:45%">Title</th>
                <th style="width:35%">Description</th>
                <th style="width:10%">Valide</th>
            </tr>
        </thead>
        <tbody>
        @php
            $steps = $maintenance->steps ?? [];
        @endphp
        @forelse($steps as $i => $step)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $step['title'] ?? '-' }}</td>
                <td>{{ $step['description'] ?? '-' }}</td>
                <td class="valide"></td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">No steps defined.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:20px;">
        <p><strong>Prepared on:</strong> {{ now()->format('Y-m-d H:i') }}</p>
    </div>
</body>
</html>
