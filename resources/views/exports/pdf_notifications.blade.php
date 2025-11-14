<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notifications Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { margin-bottom: 10px; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }
        table, th, td {
            border: 1px solid #888;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th { background: #f2f2f2; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Notifications Report</h2>
        <div>Generated: {{ now()->format('M d, Y H:i:s') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Alert Type</th>
                <th>Title</th>
                <th class="center">Message</th>
                <th>Alert Urgency</th>
                <th>Creation Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($notifications as $index => $notif)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $notif->type }}</td>
                    <td>{{ $notif->title }}</td>
                    <td class="msg-cell">{{ $notif->message ?? '' }}</td>
                    <td>{{ $notif->level }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($notif->created_at)->format('M d, Y H:i:s') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">No notifications found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
