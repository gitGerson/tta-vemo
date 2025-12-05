<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Harian</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h3>Laporan Harian</h3>
    <p>
        Tanggal: {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}<br>
        Kategori: {{ $category }}<br>
        Perusahaan: {{ $company->code ?? 'Semua' }}
    </p>
    @php
        $allowedPlates = collect();
        if ($category === 'UMUM' || $category === 'PRIBADI') {
            $allowedPlates = $vehicles
                ->filter(function ($v) use ($category) {
                    return $category === 'PRIBADI' ? (int) $v->isprivate === 1 : (int) $v->isprivate === 0;
                })
                ->flatMap(function ($v) { return array_filter([$v->plat_new, $v->plat_old]); })
                ->values();
        }
    @endphp
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
                @php $include = $category === 'KESELURUHAN' || $allowedPlates->contains($row->no_pol); @endphp
                @continue(!$include)
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
