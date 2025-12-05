<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Bulanan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h3>Laporan Bulanan</h3>
    <p>
        Perusahaan: {{ $company->code ?? 'Semua' }}<br>
        Unit: {{ $unit ?? 'Semua' }}<br>
        Bulan/Tahun: {{ $date->translatedFormat('F Y') }}
    </p>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Supir 1</th>
                <th>Supir 2</th>
                <th>No Polisi</th>
                <th>Tujuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->date)->translatedFormat('d F Y') }}</td>
                    <td>{{ $row->driver_1 ? strtoupper($row->driver_1) : 'NONE' }}</td>
                    <td>{{ $row->driver_2 ? strtoupper($row->driver_2) : 'NONE' }}</td>
                    <td>{{ $row->no_pol }}</td>
                    <td>{{ $row->destination }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
