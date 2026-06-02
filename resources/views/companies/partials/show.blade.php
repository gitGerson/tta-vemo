<table class="table table-sm mb-0">
    <tr><th class="w-25">Kode</th><td>{{ $company->code }}</td></tr>
    <tr><th>Nama</th><td>{{ $company->name }}</td></tr>
    <tr><th>Keterangan</th><td>{{ $company->desc ?: '-' }}</td></tr>
</table>
