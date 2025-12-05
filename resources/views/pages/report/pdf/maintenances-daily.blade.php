<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemeliharaan Per Unit</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h3>Laporan Pemeliharaan Per Unit</h3>
    <p>
        Periode: {{ \Carbon\Carbon::parse($start_date)->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($end_date)->translatedFormat('d F Y') }}<br>
        Unit: {{ $unit->plat_new ?? $unit->plat_old ?? 'N/A' }} {{ $unit ? '/ ' . $unit->type : '' }}
    </p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Jenis</th>
                <th>Total Qty</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detailMtc as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->maintenance_name }}</td>
                    <td>{{ $row->total_qty }}</td>
                    <td>{{ number_format($row->total_price, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
