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
        <div class="alert alert-danger alert-dismissible fade show" id="alert" style="display: none;">
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div id="alert-content"></div>
        </div>

        <!-- Loader Spinner (optional - custom or Bootstrap 5) -->
        <div id="loader-wrapper" class="text-center" style="display: none;">
            <div class="spinner-border text-danger" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    </div>
</div>

<div class="col-xl">
    <div class="card mb-6">
        <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Buat Catatan Baru</h5>
        <button type="button" onclick="history.back()" class="btn btn-secondary">Kembali ke list</button>
        </div>
        <div class="card-body">
        <form action="{{ route('record.update', $record->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                <label for="company_id" class="form-label">Perusahaan<small class="text-danger">*</small></label>
                    <select class="form-select select2" id="company_id" name="company_id">
                        <option selected disabled>- pilih perusahaan -</option>
                        @foreach ($company as $row)
                            <option {{ $record->company_id == $row->id ? 'selected' : '' }}
                                value="{{ $row->id }}">{{ $row->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                <label class="form-label" for="date">Tanggal<small class="text-danger">*</small></label>
                <div class="input-group input-group-merge">
                    <span id="basic-icon-default-company2" class="input-group-text"><i
                        class="bx bx-calendar"></i></span>
                    <input type="date" class="form-control" name="date"
                    value="{{ $record->date }}" />
                </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                <label for="driver_1" class="form-label">Supir 1<small class="text-danger">*</small></label>
                    <select class="form-select select2" id="driver_1" name="driver_1" >
                        <option selected value="{{ $record->driver_1 }}">{{ $record->driver_1 }} --dipilih
                        </option>
                        @foreach ($driver as $row)
                            @if ($row->name != $record->driver_1)
                                <option {{ $record->driver_1 == $row->id ? 'selected' : '' }}
                                    value="{{ $row->id }}">{{ $row->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                <label for="driver_2" class="form-label">Supir 2</label>
                    <select class="form-select select2" id="driver_2" name="driver_2">
                        <option value="">- pilih supir 2 -</option>
                        <option selected value="{{ $record->driver_2 }}">
                            @if ($record->driver_2)
                                {{ $record->driver_2 }} --dipilih
                            @else
                                Belum ditentutan !
                            @endif
                        </option>
                        @foreach ($driver as $row)
                            @if ($row->name != $record->driver_1 && $row->name != $record->driver_2)
                                <option value="{{ $row->id }}">{{ $row->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                <label for="no_pol" class="form-label">No Polisi<small class="text-danger">*</small></label>
                    <select class="form-select select2" id="no_pol" name="no_pol">
                        <option selected disabled>- pilih kendaraan -</option>
                        @foreach ($vehicles as $row)
                            <option {{ $record->no_pol == $row->plat_new ? 'selected' : '' }}
                                value="{{ $row->plat_new }}"
                                data-company-id="{{ $row->company_id }}"
                                data-mileage="{{ $row->mileage }}">{{ $row->plat_new }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                <label for="destination" class="form-label">Tujuan<small class="text-danger">*</small></label>
                    <select class="form-select select2" id="destination" name="destination">
                        <option selected disabled>- pilih tujuan -</option>
                       @foreach ($destination as $row)
                            <option {{ $record->destination == $row->destination ? 'selected' : '' }}
                                value="{{ $row->destination }}">{{ $row->destination }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="start_km">KM Awal</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-map"></i></span>
                        <input type="text" inputmode="numeric" id="start_km" name="start_km"
                            class="form-control validate-number"
                            placeholder="Masukkan KM Awal" value="{{ $record->start_km }}">
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label" for="finish_km">KM Akhir <small class="text-danger">*</small></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-map"></i></span>
                        <input type="text" inputmode="numeric" id="finish_km" name="finish_km"
                            class="form-control validate-number"
                            placeholder="Masukkan KM Akhir" value="{{ $record->finish_km }}">
                    </div>
                    <small class="text-danger text-xs" style="display: none;"
                        id="validateTruncatedValue">Nilai KM AKHIR jangan lebih kecil dari KM AWAL</small>
                </div>
            </div>


            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="bbm">BBM/Liter <small class="text-danger">*</small></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-car"></i></span>
                        <input type="text" inputmode="numeric" id="bbm" name="bbm"
                            class="form-control dotInput"
                            placeholder="Masukkan Nominal BBM/Liter" value="{{ $record->bbm }}">
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label" for="idr">IDR <small class="text-danger">*</small></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-money"></i></span>
                        <input type="text" inputmode="numeric" id="idr" name="idr"
                            class="form-control validate-number"
                            placeholder="Masukkan Nominal Rupiah" value="{{ $record->idr }}">
                    </div>
                </div>
            </div>

            
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
        </div>
    </div>
    </div>
</div>
</div>
@endsection

@push('addon-style')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--single {
            height: 40px;
            display: flex;
            align-items: center;
        }
    </style>
@endpush

@push('addon-script')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
            filterVehicleOptions();
        });

        function filterVehicleOptions() {
            const companyId = $('#company_id').val();
            const currentVehicle = $('#no_pol').val();

            $('#no_pol option').each(function() {
                const optionCompanyId = String($(this).data('company-id') || '');
                const shouldShow = !$(this).val() || (companyId && optionCompanyId === String(companyId));
                $(this).prop('disabled', !shouldShow);
            });

            if (currentVehicle && $('#no_pol option:selected').prop('disabled')) {
                $('#no_pol').val(null);
                $('#start_km').val('');
            }

            $('#no_pol').trigger('change.select2');
        }

        $('#company_id').on('change', function() {
            filterVehicleOptions();
        });
        {{--  menage check driver 2  --}}
        $("#driver_1").on("change", function(event) {
            event.preventDefault();
            var driver1Value = $(this).val();
            $.ajax({
                method: "GET",
                url: "/checkDriver",
                data: {
                    driver_1: driver1Value
                },
                success: function(response) {
                    $("#driver_2").empty().append(
                        '<option selected disabled>- pilih supir 2 -</option>');

                    response.data.forEach(el => {
                        $("#driver_2").append(
                            `<option value='${el.name}'> ${el.name}</option>`
                        );
                    });

                    $('#driver_2').select2();
                },
                error: function(error) {
                    console.error(error);
                }
            });
        });

        {{-- validate trucanted value finish km  --}}
        $('input[name="start_km"], input[name="finish_km"]').on('input', function(e) {
            let start_km = parseFloat($('#start_km').val());
            let finish_km = parseFloat($('#finish_km').val());
            if (finish_km && start_km && finish_km < start_km) {
                $('#validateTruncatedValue').show();
            } else {
                $('#validateTruncatedValue').hide();
            }
        });

        // Menangani pengambilan KM master
        $("#no_pol").on("change", function(event) {
            event.preventDefault();
            var selectedOption = $(this).find('option:selected');
            var mileage = selectedOption.data('mileage');
            $('#start_km').val(mileage);
        });

        {{--  validate number  --}}
        $('.validate-number').on('keypress', function(event) {
            if (event.which < 48 || event.which > 57) {
                event.preventDefault();
            }
        });
        $('.dotInput').on('input', function() {
            $(this).val($(this).val().replace(/[^0-9.]/g, ''));
        });
    </script>
@endpush
