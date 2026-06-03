<table class="table table-sm mb-0">
    <tr><th class="w-25">Kode</th><td>{{ $destination->code }}</td></tr>
    <tr><th>Kota/Kabupaten</th><td>{{ $destination->name }}</td></tr>
    <tr><th>Kode Provinsi</th><td>{{ $destination->province_code }}</td></tr>
    <tr><th>Meta</th><td>{{ $destination->meta ?: '-' }}</td></tr>
</table>
