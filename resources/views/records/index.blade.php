@extends('layouts.sneat')

@section('title')
    Data Perjalanan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Filter bar --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('records.index') }}" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label" for="start_date">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $startDate }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="end_date">Tanggal Selesai</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $endDate }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label" for="company">Perusahaan</label>
                        <select id="company" name="company" class="form-select form-select-sm">
                            <option value="">Semuanya</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" @selected((int) $companyId === $company->id)>{{ $company->code ?? $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <x-table :table="$table" engine="tabulator" layout="fitDataFill" title="Catatan Perjalanan">
            <x-slot:actions>
                <div class="d-flex gap-2">
                    @if(auth()->user()->hasPermission('operational.records.delete'))
                        <a href="{{ route('records.trash') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-trash me-1"></i> Sampah
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('operational.records.view'))
                        <a href="{{ route('records.export', array_filter(['start_date' => $startDate, 'end_date' => $endDate, 'company' => $companyId])) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-export me-1"></i> Export Excel
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('operational.records.create'))
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-record" data-bs-toggle="modal" data-bs-target="#record-modal">
                            <i class="bx bx-plus me-1"></i> Tambah
                        </button>
                    @endif
                </div>
            </x-slot:actions>
        </x-table>
    </div>

    @include('records.partials.modal')
@endsection
