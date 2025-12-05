<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Pemeliharaan Tahunan</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #000; padding: 6px; text-align: center; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <h3>Laporan Pemeliharaan Tahunan {{ $year }}</h3>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kendaraan</th>
                @foreach (['JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGU', 'SEP', 'OKT', 'NOV', 'DES'] as $month)
                    <th>{{ $month }}</th>
                @endforeach
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $monthlyTotals = array_fill(1, 12, 0);
                $overallTotal = 0;
            @endphp

            @foreach ($mtc->groupBy('no_pol') as $noPol => $rows)
                @php
                    $vehicle = $vehicles->where('plat_new', $noPol)->first();
                    $totalAmount = 0;
                    $monthlySums = array_fill(1, 12, 0);

                    foreach ($rows as $row) {
                        $monthlySums[$row->month] += $row->total_amount;
                        $monthlyTotals[$row->month] += $row->total_amount;
                        $totalAmount += $row->total_amount;
                    }

                    $overallTotal += $totalAmount;
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $vehicle ? $vehicle->plat_new . ' / ' . $vehicle->type : $noPol }}</td>
                    @for ($month = 1; $month <= 12; $month++)
                        <td>{{ number_format($monthlySums[$month]) }}</td>
                    @endfor
                    <td>{{ number_format($totalAmount) }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="2"><strong>TOTAL</strong></td>
                @foreach ($monthlyTotals as $total)
                    <td><strong>{{ number_format($total) }}</strong></td>
                @endforeach
                <td><strong>{{ number_format($overallTotal) }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
