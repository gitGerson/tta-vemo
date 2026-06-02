<div class="row g-4">
    <div class="col-md-4">
        <img
            src="{{ $driver->imageUrl() ?? asset('assets/driver_avatar.png') }}"
            alt="{{ $driver->name }}"
            class="img-fluid rounded border w-100"
        >
    </div>
    <div class="col-md-8">
        <table class="table table-sm mb-0">
            <tr><th class="w-25">Nama</th><td>{{ $driver->name }}</td></tr>
            <tr><th>NIK</th><td>{{ $driver->nik ?: '-' }}</td></tr>
            <tr><th>No Identitas</th><td>{{ $driver->no_identity }}</td></tr>
            <tr><th>Alamat</th><td>{{ $driver->address }}</td></tr>
            <tr><th>Tempat</th><td>{{ $driver->residence }}</td></tr>
            <tr><th>Tanggal Lahir</th><td>{{ $driver->date_birth?->format('d M Y') }}</td></tr>
            <tr><th>Nomor HP 1</th><td>{{ $driver->no_phone_1 }}</td></tr>
            <tr><th>Nomor HP 2</th><td>{{ $driver->no_phone_2 }}</td></tr>
            <tr><th>Tanggal Masuk</th><td>{{ $driver->entry_date?->format('d M Y') }}</td></tr>
            <tr><th>Tanggal Keluar</th><td>{{ $driver->out_date?->format('d M Y') ?: '-' }}</td></tr>
            <tr><th>Status</th><td>{{ $driver->status }}</td></tr>
        </table>
    </div>
</div>
