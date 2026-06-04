<table class="table table-sm mb-0">
    <tr><th class="w-25">Nama</th><td>{{ $fuelType->name }}</td></tr>
    <tr><th>Tipe</th><td>{{ $fuelType->type }}</td></tr>
    <tr><th>Harga</th><td>Rp {{ number_format((float) $fuelType->price, 0, ',', '.') }}</td></tr>
    <tr><th>Merk</th><td>{{ $fuelType->merk }}</td></tr>
</table>
