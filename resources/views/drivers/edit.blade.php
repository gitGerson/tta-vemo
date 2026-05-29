@extends('layouts.sneat')

@section('title')
    Edit Driver
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Driver: {{ $driver->name }}</h5>
            </div>
            <div class="card-body">
                @include('drivers.partials.form', [
                    'driver' => $driver,
                    'action' => route('drivers.update', $driver),
                    'method' => 'PUT',
                    'submitLabel' => 'Perbarui',
                ])
            </div>
        </div>
    </div>
@endsection
