@extends('layouts.sneat')

@section('title')
    Daftar Asuransi Kendaraan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-table :table="$table" engine="tabulator" layout="fitDataStretch" title="Daftar Asuransi Kendaraan">
            <x-slot:actions>
                <div class="d-flex gap-2">
                    @if(auth()->user()->hasPermission('master.vehicle_insurances.delete'))
                        <a href="{{ route('vehicle-insurances.trash') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-trash me-1"></i> Sampah
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('master.vehicle_insurances.create'))
                        <a href="{{ route('vehicle-insurances.create') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-plus me-1"></i> Tambah Asuransi
                        </a>
                    @endif
                </div>
            </x-slot:actions>
        </x-table>
    </div>
@endsection
