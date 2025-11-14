<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bin Readings Report</title>
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
        <h2>Bin Readings Report</h2>
        <div>Generated: {{ now()->format('M d, Y H:i:s') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>ID</th>
                <th>Bin Location</th>
                <th class="center">Fill Level</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bin_readings as $index => $reading)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $reading->id }}</td>
                    <td>
                        {{-- Prefer relationship if available; otherwise fallback to quick lookup by bin_id --}}
                        @if(method_exists($reading, 'bin') && $reading->relationLoaded('bin'))
                            {{ $reading->bin->name ?? '—' }}
                        @elseif(isset($reading->bin) && is_object($reading->bin))
                            {{ $reading->bin->name ?? '—' }}
                        @else
                            {{ \App\Models\Bin::find($reading->bin_id)->name ?? ('#' . ($reading->bin_id ?? '—')) }}
                        @endif
                    </td>
                    <td class="center">
                        {{-- Display fill level as percentage if numeric, otherwise raw --}}
                        @if(is_numeric($reading->fill_level))
                            {{ number_format($reading->fill_level, 0) }}%
                        @else
                            {{ $reading->fill_level }}
                        @endif
                    </td>
                    <td>{{ \Illuminate\Support\Carbon::parse($reading->created_at)->format('M d, Y H:i:s') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center">No bin readings found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
