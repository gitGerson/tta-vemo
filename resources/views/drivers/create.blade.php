@extends('layouts.sneat')

@section('title')
    Tambah Driver
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Tambah Driver</h5>
            </div>
            <div class="card-body">
                @include('drivers.partials.form', [
                    'action' => route('drivers.store'),
                    'method' => 'POST',
                    'submitLabel' => 'Simpan',
                ])
            </div>
        </div>
    </div>
@endsection
