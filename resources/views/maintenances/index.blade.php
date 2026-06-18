@extends('layouts.sneat')

@section('title')
    Data Pemeliharaan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filter bar --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('maintenances.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="start_date">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="end_date">Tanggal Selesai</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="status">Status</label>
                        <select id="status" name="status" class="form-select form-select-sm">
                            <option value="">Semua Status</option>
                            <option value="DRAFT" @selected($status === 'DRAFT')>DRAFT</option>
                            <option value="COMPLETED" @selected($status === 'COMPLETED')>SELESAI</option>
                            <option value="CANCELLED" @selected($status === 'CANCELLED')>DIBATALKAN</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <x-table :table="$table" engine="tabulator" layout="fitDataStretch" title="Daftar Perbaikan Kendaraan">
            <x-slot:actions>
                <div class="d-flex gap-2">
                    @if(auth()->user()->hasPermission('operational.maintenances.delete'))
                        <a href="{{ route('maintenances.trash') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-trash me-1"></i> Trash
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('operational.maintenances.create'))
                        <a href="{{ route('maintenances.create') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-plus me-1"></i> Add New
                        </a>
                    @endif
                </div>
            </x-slot:actions>
        </x-table>
    </div>
@endsection
