@php
    $meta = is_string($destination->meta) ? json_decode($destination->meta, true) : null;
    $latitude = is_array($meta) ? $meta['lat'] ?? $meta['latitude'] ?? null : null;
    $longitude = is_array($meta) ? $meta['long'] ?? $meta['lng'] ?? $meta['longitude'] ?? null : null;
    $hasCoordinates = is_numeric($latitude) && is_numeric($longitude);
@endphp

<table class="table table-sm mb-3">
    <tr><th class="w-25">Kode</th><td>{{ $destination->code }}</td></tr>
    <tr><th>Kota/Kabupaten</th><td>{{ $destination->name }}</td></tr>
    <tr><th>Provinsi</th><td>{{ $destination->province?->name ?? '-' }}</td></tr>
</table>

<div>
    <div class="text-uppercase text-muted small fw-semibold mb-2">Peta</div>

    @if ($hasCoordinates)
        <div
            class="destination-map-preview"
            data-latitude="{{ (float) $latitude }}"
            data-longitude="{{ (float) $longitude }}"
            data-name="{{ $destination->name }}"
        ></div>
        <div class="small text-muted mt-2">
            {{ (float) $latitude }}, {{ (float) $longitude }}
        </div>
    @else
        <div class="alert alert-secondary mb-0">Koordinat peta tidak tersedia.</div>
    @endif
</div>
