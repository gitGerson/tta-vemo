<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Harian</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h3 { margin: 0 0 8px; text-align: center; }
        .report-meta { width: 100%; margin-bottom: 0; }
        .report-meta td { border: 0; padding: 0; }
        .report-meta .meta-right { text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        .report-table { margin-top: 0; }
        th, td { border: 1px solid #000; padding: 3px; vertical-align: middle; white-space: nowrap; }
        th { background: #f2f2f2; text-align: center; }
        td.text-center { text-align: center; }
        td.text-right { text-align: right; }
        .signature-space { height: 60px; }
    </style>
</head>
<body>
    <h3>LAPORAN HARIAN BAHAN BAKAR MINYAK (BBM)</h3>
    @php
        $vehiclesByPlate = $vehicles->flatMap(function ($vehicle) {
            return collect(array_filter([$vehicle->plat_new]))
                ->mapWithKeys(fn ($plate) => [$plate => $vehicle]);
        });
    @endphp
    <table class="report-meta">
        <tr>
            <td><strong>{{ $company->name ?? 'Semua' }}</strong></td>
            <td class="meta-right"><strong>Tanggal: {{ \Carbon\Carbon::parse($date)->locale('id')->translatedFormat('d F Y') }}</strong></td>
        </tr>
    </table>
    <table class="report-table">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">JENIS</th>
                <th rowspan="2">NOPOL</th>
                <th rowspan="2">TUJUAN</th>
                <th colspan="3">KILOMETER</th>
                <th rowspan="2">LITER</th>
                <th rowspan="2">RUPIAH</th>
                <th rowspan="2">SUPIR</th>
                <th rowspan="2">SKALA</th>
            </tr>
            <tr>
                <th>AWAL</th>
                <th>AKHIR</th>
                <th>TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php $rowNumber = 1; @endphp
            @foreach ($records as $row)
                @php
                    $vehicle = $vehiclesByPlate->get($row->no_pol);
                @endphp
                <tr>
                    <td class="text-center">{{ $rowNumber++ }}</td>
                    <td>{{ $vehicle?->type ?? '-' }}</td>
                    <td>{{ $row->no_pol }}</td>
                    <td>{{ $row->destination }}</td>
                    <td class="text-right">{{ $row->start_km ?? 0 }}</td>
                    <td class="text-right">{{ $row->finish_km ?? 0 }}</td>
                    <td class="text-right">{{ $row->totals ?? 0 }}</td>
                    <td class="text-right">{{ $row->bbm ?? 0 }}</td>
                    <td class="text-right">{{ number_format((float) ($row->idr ?? 0), 0, ',', '.') }}</td>
                    <td>{{ strtoupper($row->driver_1 ?: 'NONE') }} - {{ strtoupper($row->driver_2 ?: 'NONE') }}</td>
                    <td class="text-center">{{ $row->scale ?? '-' }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="6" class="text-left"><strong>JUMLAH</strong></td>
                <td class="text-right"><strong>{{ $records->sum(fn ($row) => (float) ($row->totals ?? 0)) }}</strong></td>
                <td class="text-right"><strong>{{ $records->sum(fn ($row) => (float) ($row->bbm ?? 0)) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($records->sum(fn ($row) => (float) ($row->idr ?? 0)), 0, ',', '.') }}</strong></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="6" rowspan="2">&nbsp;</td>
                <td colspan="3" class="text-center"><strong>Mengetahui</strong></td>
                <td colspan="2" class="text-center"><strong>Dibuat Oleh</strong></td>
            </tr>
            <tr>
                <td colspan="3" class="signature-space">&nbsp;</td>
                <td colspan="2" class="signature-space">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="6">
                    Printed at: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y H:i') }}<br>
                    Dicetak oleh: {{ auth()->user()?->name ?? '-' }}
                </td>
                <td colspan="3">&nbsp;</td>
                <td colspan="2">&nbsp;</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
