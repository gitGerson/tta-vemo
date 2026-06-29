@extends('layouts.sneat')

@section('title')
    Ubah Data Perjalanan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Ubah Data Perjalanan</h4>
            <a href="{{ route('records.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @include('records.partials.form', [
                    'record' => $record,
                    'action' => route('records.update', $record),
                    'method' => 'PUT',
                    'submitLabel' => 'Perbarui',
                ])
            </div>
        </div>
    </div>
@endsection
