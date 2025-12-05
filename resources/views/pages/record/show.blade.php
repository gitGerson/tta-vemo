@extends('layouts.sneat')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">

        <div class="row mb-3">
            <div class="col-12">
                <!-- Notifications -->
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                <!-- Page Heading -->

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-xl">
            <div class="card mb-6">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Detail Catatan Perjalanan</h5>
                    <a href="{{ route('record.index') }}" class="btn btn-secondary">Kembali ke list</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="basic-icon-default-fullname">Perusaahaan</label>
                            <div class="input-group input-group-merge">
                                <input type="name" readonly class="form-control" value="{{ $record->company->name }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="basic-icon-default-email">Tanggal</label>
                            <div class="input-group input-group-merge">
                                <input type="text" readonly class="form-control"
                                    value="{{ Carbon\Carbon::parse($record->date)->translatedFormat('d F Y') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="driver_1">Supir 1</label>
                            <div class="input-group input-group-merge">
                                <input type="text" id="driver_1" readonly class="form-control"
                                    value="{{ $record->driver_1 }}">
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="driver_2">Supir 2</label>
                            <div class="input-group input-group-merge">
                                <input type="text" id="driver_2" readonly class="form-control"
                                    value="{{ $record->driver_2 ?? 'NONE' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="date">No Polisi</label>
                            <div class="input-group input-group-merge">
                                <input type="text" readonly class="form-control" value="{{ $record->no_pol }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="basic-icon-default-phone">Destination</label>
                            <div class="input-group input-group-merge">
                                <input type="text" readonly class="form-control" value="{{ $record->destination }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="entry_date">KM Awal</label>
                            <div class="input-group input-group-merge">
                                <input type="text" readonly class="form-control" value="{{ $record->start_km }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="basic-icon-default-phone">KM Akhir</label>
                            <div class="input-group input-group-merge">
                                <input type="text" readonly class="form-control" value="{{ $record->finish_km }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="entry_date">BBM/Liter</label>
                            <div class="input-group input-group-merge">
                                <input type="text" inputmode="decimal" readonly class="form-control"
                                    value="{{ $record->bbm }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="basic-icon-default-phone">IDR</label>
                            <div class="input-group input-group-merge">
                                <input type="text" readonly class="form-control"
                                    value="Rp. {{  formatNominal($record->idr) }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="entry_date">Total</label>
                            <div class="input-group input-group-merge">
                                <input type="text" readonly class="form-control"
                                    value="{{ abs($record->finish_km - $record->start_km) }}">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="basic-icon-default-phone">Skala</label>
                            <div class="input-group input-group-merge">
                                <input type="text" readonly class="form-control" value="{{ $record->scale }}">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('addon-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('addon-script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2();
        });
    </script>
@endpush