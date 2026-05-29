@extends('layouts.sneat')

@section('title')
    Detail Driver
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Driver: {{ $driver->name }}</h5>
                <div class="d-flex gap-2">
                    @if(auth()->user()->hasPermission('master.drivers.edit'))
                        <a href="{{ route('drivers.edit', $driver) }}" class="btn btn-warning">
                            <i class="bx bx-edit me-1"></i> Edit
                        </a>
                    @endif
                    <a href="{{ route('drivers.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-3">
                        <img
                            src="{{ $driver->imageUrl() ?? asset('assets/driver_avatar.png') }}"
                            alt="{{ $driver->name }}"
                            class="img-fluid rounded border"
                        >
                    </div>
                    <div class="col-md-9">
                        <table class="table table-sm">
                            <tr><th>Nama</th><td>{{ $driver->name }}</td></tr>
                            <tr><th>NIK</th><td>{{ $driver->nik ?: '-' }}</td></tr>
                            <tr><th>No Identitas</th><td>{{ $driver->no_identity }}</td></tr>
                            <tr><th>Alamat</th><td>{{ $driver->address }}</td></tr>
                            <tr><th>Tempat</th><td>{{ $driver->residence }}</td></tr>
                            <tr><th>Tanggal Lahir</th><td>{{ $driver->date_birth?->format('d M Y') }}</td></tr>
                            <tr><th>HP</th><td>{{ $driver->no_phone_1 }}</td></tr>
                            <tr><th>HP Wali</th><td>{{ $driver->no_phone_2 }}</td></tr>
                            <tr><th>Tanggal Masuk</th><td>{{ $driver->entry_date?->format('d M Y') }}</td></tr>
                            <tr><th>Tanggal Keluar</th><td>{{ $driver->out_date?->format('d M Y') ?: '-' }}</td></tr>
                            <tr><th>Status</th><td>{{ $driver->status }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
