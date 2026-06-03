<table class="table table-sm mb-0">
    <tr><th class="w-25">Kode</th><td>{{ $company->code }}</td></tr>
    <tr><th>Nama</th><td>{{ $company->name }}</td></tr>
    <tr><th>Email</th><td>{{ $company->email ?: '-' }}</td></tr>
    <tr><th>Telepon</th><td>{{ $company->phone ?: '-' }}</td></tr>
    <tr><th>Gambar</th><td>{{ $company->image ?: '-' }}</td></tr>
    <tr><th>Keterangan</th><td>{{ $company->notes ?: '-' }}</td></tr>
</table>
