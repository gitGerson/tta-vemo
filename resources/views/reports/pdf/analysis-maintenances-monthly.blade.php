<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Analisa Bulanan Pemeliharaan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }
        h3 { margin: 0 0 8px; text-align: center; letter-spacing: 0.4px; }
        .report-meta { width: 100%; margin-bottom: 2px; }
        .report-meta td { border: 0; padding: 1px 0; vertical-align: top; }
        .report-meta .meta-right { text-align: right; }
        .meta-block { width: auto; border-collapse: collapse; }
        .meta-block td { border: 0; padding: 1px 0; vertical-align: top; }
        .meta-label { width: 62px; }
        .meta-colon { width: 10px; text-align: center; }
        table { width: 100%; border-collapse: collapse; }
        .report-table { margin-top: 0; }
        th, td { border: 1px solid #000; padding: 3px 4px; vertical-align: middle; white-space: nowrap; }
        th { background: #f2f2f2; text-align: center; }
        td.text-center { text-align: center; }
        td.text-right { text-align: right; }
        td.text-left { text-align: left; }
        .footer-label { font-size: 8px; line-height: 1.4; }
        .signature-title { font-weight: bold; text-align: center; }
        .signature-space { height: 60px; }
    </style>
</head>
<body>
    <h3>LAPORAN ANALISA BULANAN PEMELIHARAAN</h3>
    @php
        $totalCount = $rows->sum('maintenance_count');
        $totalCost = $rows->sum('total_cost');
    @endphp
    <table class="report-meta">
        <tr>
            <td>
                <table class="meta-block">
                    <tr>
                        <td class="meta-label"><strong>Company</strong></td>
                        <td class="meta-colon"><strong>:</strong></td>
                        <td><strong>{{ $company->name ?? 'Semua Perusahaan' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="meta-label"><strong>Periode</strong></td>
                        <td class="meta-colon"><strong>:</strong></td>
                        <td><strong>{{ $date->locale('id')->translatedFormat('F Y') }}</strong></td>
                    </tr>
                </table>
            </td>
            <td class="meta-right">
                <table class="meta-block" style="margin-left: auto;">
                    <tr>
                        <td class="meta-label"><strong>No. Polisi</strong></td>
                        <td class="meta-colon"><strong>:</strong></td>
                        <td><strong>{{ $unit ?: 'Semua Unit' }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="report-table">
        <thead>
            <tr>
                <th>No</th>
                <th>NOPOL</th>
                <th>JENIS</th>
                <th>JUMLAH PEMELIHARAAN</th>
                <th>TOTAL BIAYA</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $row['no_pol'] }}</td>
                    <td>{{ $row['jenis'] }}</td>
                    <td class="text-right">{{ $row['maintenance_count'] }}</td>
                    <td class="text-right">{{ number_format((float) $row['total_cost'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3" class="text-left"><strong>JUMLAH</strong></td>
                <td class="text-right"><strong>{{ number_format($totalCount, 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totalCost, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <td colspan="2" rowspan="2">&nbsp;</td>
                <td colspan="2" class="signature-title">Mengetahui</td>
                <td class="signature-title">Dibuat Oleh</td>
            </tr>
            <tr>
                <td colspan="2" class="signature-space">&nbsp;</td>
                <td class="signature-space">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="3" class="footer-label">
                    Printed at: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y H:i') }}<br>
                    Dicetak oleh: {{ auth()->user()?->name ?? '-' }}
                </td>
                <td colspan="2">&nbsp;</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
