@php
    // Human-readable label per reminder type, falling back to a generic heading. Keeps one
    // digest template able to render every reminder produced by NotificationService.
    $labels = [
        'maintenance.awaiting-confirmation' => 'Maintenance menunggu konfirmasi',
        'maintenance.stale-draft' => 'Maintenance perlu tindak lanjut',
        'vehicle.service-due' => 'Kendaraan jatuh tempo servis',
        'vehicle.photo-missing' => 'Foto kendaraan belum lengkap',
        'vehicle.insurance-overdue' => 'Asuransi kedaluwarsa',
        'vehicle.insurance-expiring' => 'Asuransi akan berakhir',
        'vehicle.stnk-due' => 'Tenggat Waktu STNK',
        'vehicle.kir-due' => 'Tenggat Waktu KIR',
    ];

    $grouped = collect($items)->groupBy('type');

    // Escape Markdown table delimiters so a stray pipe in a subtitle can't break a row.
    $cell = fn (string $text): string => str_replace(['|', "\n"], ['\|', ' '], $text);
@endphp

@component('mail::message')
# Pengingat VEMO

Halo {{ $greetingName }}, berikut **{{ count($items) }}** item yang memerlukan perhatian Anda.

@foreach ($grouped as $type => $group)
## {{ $labels[$type] ?? 'Pengingat' }} ({{ count($group) }})

@component('mail::table')
| Kendaraan | Detail | |
| :--- | :--- | :---: |
@foreach ($group as $item)
| {{ $cell($item['title']) }} | {{ $cell($item['subtitle']) }} | [Buka]({{ $item['link'] }}) |
@endforeach
@endcomponent
@endforeach

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
