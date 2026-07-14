<table class="table table-sm mb-0">
    <tr><th class="w-25">Kode</th><td>{{ $vehicleType->code }}</td></tr>
    <tr><th>Nama</th><td>{{ $vehicleType->name }}</td></tr>
    <tr><th>Deskripsi</th><td>{{ $vehicleType->descriptions ?: '-' }}</td></tr>
</table>
