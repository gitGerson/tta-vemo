<table class="table table-sm mb-0">
    <tr><th class="w-25">Tipe</th><td>{{ $vehicle->type }}</td></tr>
    <tr><th>Perusahaan</th><td>{{ $vehicle->company?->name ?? '-' }}</td></tr>
    <tr><th>Tahun Masuk</th><td>{{ $vehicle->entry_year }}</td></tr>
    <tr><th>Plat Baru</th><td>{{ $vehicle->plat_new }}</td></tr>
    <tr><th>Plat Lama</th><td>{{ $vehicle->plat_old ?: '-' }}</td></tr>
    <tr><th>Kilometer</th><td>{{ $vehicle->mileage ?? '0' }}</td></tr>
    <tr><th>Bahan Bakar</th><td>{{ $vehicle->fuel_type }}</td></tr>
    <tr><th>Status</th><td>{{ $vehicle->status }}</td></tr>
    <tr><th>Maintenance</th><td>{{ $vehicle->mtc }}</td></tr>
    <tr><th>Private</th><td>{{ $vehicle->isprivate ? 'Yes' : 'No' }}</td></tr>
    <tr><th>Keterangan</th><td>{{ $vehicle->desc ?: '-' }}</td></tr>
</table>
