<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Classifications Report</title>
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
        <h2>Classifications Report</h2>
        <div>Generated: {{ now()->format('M d, Y H:i:s') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>ID</th>
                <th>Classification</th>
                <th class="center">Confidence Score</th>
                <th>Timestamp</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($classifications as $index => $object)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $object->id }}</td>
                    <td>
                        {{ $object->classification }}
                        {{-- @if(method_exists($object, 'bin') && $object->relationLoaded('bin'))
                            {{ $object->bin->name ?? '—' }}
                        @elseif(isset($object->bin) && is_object($object->bin))
                            {{ $object->bin->name ?? '—' }}
                        @else
                            {{ \App\Models\Bin::find($object->bin_id)->name ?? ('#' . ($object->bin_id ?? '—')) }}
                        @endif --}}
                    </td>
                    <td class="center">
                        @if(is_numeric($object->score))
                            {{ number_format($object->score, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ \Illuminate\Support\Carbon::parse($object->created_at)->format('M d, Y H:i:s') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="center">No classifications found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
