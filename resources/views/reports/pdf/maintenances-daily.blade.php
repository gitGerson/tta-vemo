<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemeliharaan Per Unit</title>
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
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .footer-label { font-size: 8px; line-height: 1.4; }
        .signature-title { font-weight: bold; text-align: center; }
        .signature-space { height: 60px; }
        .summary-row td { font-weight: bold; background: #fafafa; }
    </style>
</head>
<body>
    <h3>LAPORAN PEMELIHARAAN PER UNIT</h3>
    <table class="report-meta">
        <tr>
            <td>
                <table class="meta-block">
                    <tr>
                        <td class="meta-label"><strong>Periode</strong></td>
                        <td class="meta-colon"><strong>:</strong></td>
                        <td><strong>{{ \Carbon\Carbon::parse($start_date)->locale('id')->translatedFormat('d F Y') }} - {{ \Carbon\Carbon::parse($end_date)->locale('id')->translatedFormat('d F Y') }}</strong></td>
                    </tr>
                </table>
            </td>
            <td class="meta-right">
                <table class="meta-block" style="margin-left: auto;">
                    <tr>
                        <td class="meta-label"><strong>No. Polisi</strong></td>
                        <td class="meta-colon"><strong>:</strong></td>
                        <td><strong>{{ $unit?->plat_new ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="meta-label"><strong>Jenis</strong></td>
                        <td class="meta-colon"><strong>:</strong></td>
                        <td><strong>{{ $unit?->type ?? '-' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="meta-label"><strong>Tahun Masuk</strong></td>
                        <td class="meta-colon"><strong>:</strong></td>
                        <td><strong>{{ $unit?->entry_year ?? '-' }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="report-table">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Tanggal</th>
                <th rowspan="2">Kode MT</th>
                <th rowspan="2">Jenis</th>
                <th colspan="3">Biaya</th>
            </tr>
            <tr>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detailMtc as $row)
                @php $rowDate = $row->date_out ?? $row->date_in; @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $rowDate ? \Carbon\Carbon::parse($rowDate)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $row->code_mt ?? '-' }}</td>
                    <td>{{ $row->maintenance_name }}</td>
                    <td class="text-end">{{ $row->qty ?: 0 }}</td>
                    <td class="text-end">{{ number_format((float) ($row->price ?? 0), 0, ',', '.') }}</td>
                    <td class="text-end">{{ number_format((float) ($row->subtotal ?? 0), 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="summary-row">
                <td colspan="6">JUMLAH</td>
                <td class="text-end">{{ number_format($detailMtc->sum(fn ($item) => (float) ($item->subtotal ?? 0)), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="2" rowspan="2">&nbsp;</td>
                <td colspan="3" class="signature-title">Mengetahui</td>
                <td colspan="2" class="signature-title">Dibuat Oleh</td>
            </tr>
            <tr>
                <td colspan="3" class="signature-space">&nbsp;</td>
                <td colspan="2" class="signature-space">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="2" class="footer-label">
                    Printed at: {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y H:i') }}<br>
                    Dicetak oleh: {{ auth()->user()?->name ?? '-' }}
                </td>
                <td colspan="3">&nbsp;</td>
                <td colspan="2">&nbsp;</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
