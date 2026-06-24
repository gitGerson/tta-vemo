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
    ];

    $grouped = collect($items)->groupBy('type');
@endphp

@component('mail::message')
# Pengingat VEMO

Halo {{ $greetingName }}, berikut {{ count($items) }} item yang memerlukan perhatian Anda.

@foreach ($grouped as $type => $group)
## {{ $labels[$type] ?? 'Pengingat' }}

@foreach ($group as $item)
**{{ $item['title'] }}** — {{ $item['subtitle'] }}
[Buka]({{ $item['link'] }})

@endforeach
@endforeach

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
