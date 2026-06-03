<table class="table table-sm table-striped mb-0">
    <tr><th class="w-25">Tipe</th><td>{{ $vehicle->type }}</td></tr>
    <tr><th>Perusahaan</th><td>{{ $vehicle->company?->name ?? '-' }}</td></tr>
    <tr><th>Tahun Masuk</th><td>{{ $vehicle->entry_year }}</td></tr>
    <tr><th>Plat Nomor</th><td>{{ $vehicle->plat_new }}</td></tr>
    <tr><th>Kilometer</th><td>{{ $vehicle->mileage ?? '0' }}</td></tr>
    <tr><th>Bahan Bakar</th><td>{{ $vehicle->fuel_type }}</td></tr>
    <tr><th>Status</th><td>{{ $vehicle->status }}</td></tr>
    <tr><th>Maintenance</th><td>{{ $vehicle->mtc }}</td></tr>
    <tr><th>Private</th><td>{{ $vehicle->isprivate ? 'Yes' : 'No' }}</td></tr>
    <tr><th>Nomor Mesin</th><td>{{ $vehicle->machine_number ?: '-' }}</td></tr>
    <tr><th>Nomor Rangka (VIN)</th><td>{{ $vehicle->vin_number ?: '-' }}</td></tr>
    <tr><th>Harga Beli</th><td>{{ $vehicle->purchase_price !== null ? 'Rp ' . number_format((float) $vehicle->purchase_price, 0, ',', '.') : '-' }}</td></tr>
    <tr><th>Harga Jual</th><td>{{ $vehicle->selling_price !== null ? 'Rp ' . number_format((float) $vehicle->selling_price, 0, ',', '.') : '-' }}</td></tr>
    <tr><th>Tanggal Beli</th><td>{{ $vehicle->purchase_date?->format('d M Y') ?? '-' }}</td></tr>
    <tr><th>Tanggal Jual</th><td>{{ $vehicle->sale_date?->format('d M Y') ?? '-' }}</td></tr>
    <tr><th>Keterangan</th><td>{{ $vehicle->desc ?: '-' }}</td></tr>
    <tr>
        <th>Foto BPKB</th>
        <td>
            @forelse ($vehicle->bpkbImageUrls() as $url)
                <a href="{{ $url }}" target="_blank" rel="noopener"><img src="{{ $url }}" alt="BPKB" class="rounded border me-1 mb-1" style="height: 64px; width: auto;"></a>
            @empty
                -
            @endforelse
        </td>
    </tr>
    <tr>
        <th>Foto STNK</th>
        <td>
            @forelse ($vehicle->stnkImageUrls() as $url)
                <a href="{{ $url }}" target="_blank" rel="noopener"><img src="{{ $url }}" alt="STNK" class="rounded border me-1 mb-1" style="height: 64px; width: auto;"></a>
            @empty
                -
            @endforelse
        </td>
    </tr>
</table>
