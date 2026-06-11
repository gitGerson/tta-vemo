<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Label Kendaraan - {{ $vehicle->plat_new }}</title>
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f2f2f2;
            color: #111;
        }

        .label-print-page {
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            padding: 12px;
        }

        .vehicle-label {
            width: 6.5cm;
            height: 2.4cm;
            display: grid;
            grid-template-columns: 1.76cm 1fr;
            gap: 0.06cm;
            padding: 0.02cm 0.04cm;
            background: #fff;
            overflow: hidden;
        }

        .vehicle-label__qr {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.02cm;
            text-align: center;
            align-self: start;
        }

        .vehicle-label__qr-code {
            width: 1.56cm;
            height: 1.56cm;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            font-size: 0;
            line-height: 0;
        }

        .vehicle-label__qr-code svg,
        .vehicle-label__qr-code img {
            display: block;
            width: 1.56cm;
            height: 1.56cm;
            max-width: 100%;
            max-height: 100%;
            vertical-align: top;
        }

        .vehicle-label__tag {
            font-size: 0.27cm;
            font-weight: 650;
            letter-spacing: 0.01em;
            white-space: nowrap;
            line-height: 1;
        }

        .vehicle-label__body {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            min-width: 0;
            white-space: nowrap;
            align-self: start;
        }

        .vehicle-label__title {
            margin: 0 0 0.07cm;
            font-size: 0.28cm;
            font-weight: 800;
            line-height: 1;
        }

        .vehicle-label__subtitle {
            margin: 0 0 0.04cm;
            font-size: 0.18cm;
            font-weight: 600;
            line-height: 1;
        }

        .vehicle-label__value {
            margin: 0;
            font-size: 0.245cm;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.01em;
            line-height: 1;
            white-space: nowrap;
        }

        .vehicle-label__meta {
            margin-top: 0.07cm;
            display: grid;
            gap: 0.07cm;
        }

        .vehicle-label__meta-row {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .vehicle-label__meta-label {
            margin: 0;
            font-size: 0.17cm;
            font-weight: 600;
            line-height: 1;
        }

        @media print {
            @page {
                margin: 0;
                size: 6.5cm 2.4cm;
            }

            html,
            body {
                width: 6.5cm;
                height: 2.4cm;
                margin: 0;
                background: #fff;
                overflow: hidden;
            }

            .label-print-page {
                width: 6.5cm;
                height: 2.4cm;
                min-height: 2.4cm;
                padding: 0;
                overflow: hidden;
            }

            .vehicle-label {
                margin: 0.28cm 0 0 0.2cm;
            }
        }
    </style>
</head>
<body>
    @php
        $typeLabel = $vehicle->vehicleType?->name ?? $vehicle->type;
        $brandName = $vehicle->vehicleBrand?->name;
        $typeBrand = trim(($typeLabel ?: '').($brandName ? ' / '.$brandName : '')) ?: '-';
    @endphp
    <div class="label-print-page">
        <div class="vehicle-label">
            <div class="vehicle-label__qr" data-qr-url="{{ $scanUrl }}">
                <div class="vehicle-label__qr-code">
                    {!! QrCode::size(110)->margin(0)->generate($scanUrl) !!}
                </div>
                <div class="vehicle-label__tag">{{ $vehicle->plat_new }}</div>
            </div>

            <div class="vehicle-label__body">
                <h1 class="vehicle-label__title">Property of Tong Tji Group</h1>
                <p class="vehicle-label__subtitle">Perusahaan</p>
                <p class="vehicle-label__value">{{ $vehicle->company?->name ?: '-' }}</p>

                <div class="vehicle-label__meta">
                    <div class="vehicle-label__meta-row">
                        <p class="vehicle-label__subtitle vehicle-label__meta-label">Tipe / Merek</p>
                        <p class="vehicle-label__value">{{ $typeBrand }}</p>
                    </div>
                    <div class="vehicle-label__meta-row">
                        <p class="vehicle-label__subtitle vehicle-label__meta-label">Tahun Masuk</p>
                        <p class="vehicle-label__value">{{ $vehicle->entry_year ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', function () {
            window.print();
        });
    </script>
</body>
</html>
