<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Events Export - {{ now()->format('Y-m-d H:i:s') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        @page {
            margin: 1in;
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 10px;
                color: #666;
            }
        }
        body {
            font-family: 'Inter', 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #28a745;
        }
        .header h1 {
            color: #28a745;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header p {
            color: #666;
            margin: 5px 0 0;
            font-size: 14px;
        }
        .logo-placeholder {
            width: 60px;
            height: 60px;
            background: #28a745;
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        th {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        tr:nth-child(even) {
            background: #f8f9fa;
        }
        tr:hover {
            background: #e3f2fd;
        }
        td {
            font-size: 11px;
        }
        .status-published {
            background: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 500;
        }
        .status-draft {
            background: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 500;
        }
        .no-data {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        @media print {
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-placeholder">🎉</div>
        <h1>Events Report</h1>
        <p>Generated on {{ now()->format('F j, Y \a\t g:i A') }}</p>
    </div>
    
    @if($events->isEmpty())
        <div class="no-data">
            <h3>No Events Found</h3>
            <p>Apply different filters to view events data.</p>
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Date & Time</th>
                    <th>Location</th>
                    <th>Category</th>
                    <th>Attendees</th>
                    <th>Max Participants</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                @foreach($events as $event)
                <tr>
                    <td><strong>{{ Str::limit($event->title, 30) }}</strong></td>
                    <td>{{ Str::limit($event->description ?? 'N/A', 50) }}</td>
                    <td>{{ $event->event_date->format('M j, Y g:i A') }}</td>
                    <td>{{ $event->location }}</td>
                    <td><span style="color: #28a745; font-weight: 500;">{{ $event->category?->name ?? 'No Category' }}</span></td>
                    <td>{{ $event->users_count }} / {{ $event->max_participants ?? 'Unlimited' }}</td>
                    <td>{{ $event->max_participants ?? 'Unlimited' }}</td>
                    <td>
                        @if($event->is_published)
                            <span class="status-published">Published</span>
                        @else
                            <span class="status-draft">Draft</span>
                        @endif
                    </td>
                    <td>{{ $event->user?->name ?? 'N/A' }}</td>
                    <td>{{ $event->created_at->format('M j, Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>