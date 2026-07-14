<table class="table table-sm mb-0">
    <tr><th class="w-25">Kode</th><td>{{ $vehicleBrand->code }}</td></tr>
    <tr><th>Nama</th><td>{{ $vehicleBrand->name }}</td></tr>
    <tr>
        <th>Logo</th>
        <td>
            @if($vehicleBrand->logoUrl())
                <img src="{{ $vehicleBrand->logoUrl() }}" alt="{{ $vehicleBrand->name }}" class="rounded border" style="height: 72px; width: auto;">
            @else
                -
            @endif
        </td>
    </tr>
    <tr><th>Deskripsi</th><td>{{ $vehicleBrand->descriptions ?: '-' }}</td></tr>
</table>
