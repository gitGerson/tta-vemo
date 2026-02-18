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
                </div>
                <div class="card-body">
                <form id="addForm" action="{{ route('record.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                        <label for="company_id" class="form-label">Perusahaan<small class="text-danger">*</small></label>
                            <select class="form-select select2" id="company_id" name="company_id" required>
                                <option selected disabled>- pilih perusahaan -</option>
                                @foreach ($company as $row)
                                    <option {{ old('company_id') == $row->id ? 'selected' : '' }}
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
                            value="{{ old('date') }}" disabled required />
                        </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                        <label for="driver_1" class="form-label">Supir 1<small class="text-danger">*</small></label>
                            <select class="form-select select2" id="driver_1" name="driver_1" disabled required>
                                <option selected disabled>- pilih supir 1 -</option>
                                @foreach ($driver as $row)
                                    <option {{ old('driver_1') == $row->name ? 'selected' : '' }}
                                        value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                        <label for="driver_2" class="form-label">Supir 2</label>
                            <select class="form-select select2" id="driver_2" name="driver_2" disabled>
                                <option selected disabled>- pilih supir 2 -</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                        <label for="no_pol" class="form-label">No Polisi<small class="text-danger">*</small></label>
                            <select class="form-select select2" id="no_pol" name="no_pol" disabled required>
                                <option selected disabled>- pilih kendaraan -</option>
                                @foreach ($vehicles as $row)
                                    <option {{ old('no_pol') == $row->plat_new ? 'selected' : '' }}
                                        data-mileage={{ $row->mileage }} value="{{ $row->plat_new }}">
                                        {{ $row->plat_new }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                        <label for="destination" class="form-label">Tujuan<small class="text-danger">*</small></label>
                            <select class="form-select select2" id="destination" name="destination" disabled required>
                                <option selected disabled>- pilih tujuan -</option>
                                @foreach ($destination as $row)
                                    <option {{ old('destination') == $row->destination ? 'selected' : '' }}
                                        value="{{ $row->destination }}">{{ $row->destination }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="start_km">KM Awal <small class="text-danger">*</small></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-map"></i></span>
                                <input type="text" inputmode="numeric" id="start_km" name="start_km"
                                    class="form-control validate-number"
                                    placeholder="Masukkan KM Awal" value="{{ old('start_km') }}" disabled required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="finish_km">KM Akhir <small class="text-danger">*</small></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-map"></i></span>
                                <input type="text" inputmode="numeric" id="finish_km" name="finish_km"
                                    class="form-control validate-number"
                                    placeholder="Masukkan KM Akhir" value="{{ old('finish_km') }}" disabled required>
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
                                    placeholder="Masukkan Nominal BBM/Liter" value="{{ old('bbm') }}" disabled required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="idr">IDR <small class="text-danger">*</small></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-money"></i></span>
                                <input type="text" inputmode="numeric" id="idr" name="idr"
                                    class="form-control validate-number"
                                    placeholder="Masukkan Nominal Rupiah" value="{{ old('idr') }}" disabled required>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button type="button" id="submitBtn" class="btn btn-primary" disabled>Submit</button>
                        <button type="button" id= "backBtn" onclick="history.back()" class="btn btn-secondary">Back</button>
                    </div>
                </form>
                </div>
            </div>
            </div>
</div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('custom/loading.css') }}">
    <style>
        .select2-container--default .select2-selection--single {
            height: 40px;
            display: flex;
            align-items: center;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Fixed JavaScript code for View 1
$(document).ready(function() {
    $('.select2').select2();

    // Autofill KM akhir from selected vehicle mileage
    $('#no_pol').on('change', function() {
        const mileage = $(this).find('option:selected').data('mileage');
        $('#start_km').val(mileage ?? '');
    });

    // Menangani perubahan driver_1
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
                    '<option selected disabled value="" >- pilih supir 2 -</option>',
                    ' <option value="">- NONE -</option>'
                );

                response.data.forEach(el => {
                    $("#driver_2").append(
                        `<option value='${el.id}'> ${el.name}</option>`
                    );
                });

                $('#driver_2').select2();
            },
            error: function(error) {
                console.error(error);
            }
        });
    });

    // Validasi nilai KM
    $('input[name="start_km"], input[name="finish_km"]').on('input', function(e) {
        let start_km = parseFloat($('#start_km').val());
        let finish_km = parseFloat($('#finish_km').val());
        if (finish_km && start_km && finish_km < start_km) {
            $('#validateTruncatedValue').show();
        } else {
            $('#validateTruncatedValue').hide();
        }
    });

    // Validasi angka
    $('.validate-number').on('keypress', function(event) {
        if (event.which < 48 || event.which > 57) {
            event.preventDefault();
        }
    });

    // Only allow dots
    $('.dotInput').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9.]/g, ''));
    });

    // FIXED: Mengaktifkan dan menonaktifkan form
    function toggleFormState(enable) {
        // Only enable/disable fields that should be controlled, excluding company_id
        $('form :input').not('#company_id').prop('disabled', !enable);
        // Use the correct selector for the submit button
        $('#submitBtn').prop('disabled', !enable);

        $('#backBtn').prop('disabled', false);
    }

    // Initial state - disable all fields except company_id
    toggleFormState(false);

    // FIXED: Check if company_id already has value on page load
    if ($('#company_id').val()) {
        toggleFormState(true);
    }

    // Mengaktifkan elemen form jika company_id dipilih
    $('#company_id').change(function() {
        if ($(this).val()) {
            toggleFormState(true);
        } else {
            toggleFormState(false);
        }
    });

    // Handle submit button response
    $('#submitBtn').on('click', function() {
        var form = $('#addForm')[0];
        var formData = new FormData(form);
        $('#loader-wrapper').show();
        $.ajax({
            url: form.action,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(data) {
                console.log(data)
                $('#loader-wrapper').hide();
                if (data.success) {
                    $('#alert').removeClass('alert-danger').addClass('alert-success');
                    $('#alert-content').html(data.message);
                    $('#alert').show();
                    if ($('#alert').is(':visible')) {
                        $('html, body').animate({
                            scrollTop: $('#alert').offset().top
                        }, 400);
                    }
                    setTimeout(function() {
                        window.location.href = '/record';
                    }, 1000);
                } else {
                    handleErrors(data.errors);
                }
            },
            error: function(jqXHR) {
                $('#loader-wrapper').hide();
                var response = JSON.parse(jqXHR.responseText);
                if (response.errors) {
                    handleErrors(response.errors);
                } else {
                    $('#alert').removeClass('alert-success').addClass('alert-danger');
                    $('#alert-content').html(response.message || 'An error occurred');
                    $('#alert').show();
                    if ($('#alert').is(':visible')) {
                        $('html, body').animate({
                            scrollTop: $('#alert').offset().top
                        }, 400);
                    }
                }
            }
        });
    });

    function handleErrors(errors) {
        var errorMessages = '';

        if (typeof errors === 'object') {
            errorMessages = '<ul>';
            Object.keys(errors).forEach(function(key) {
                var messages = errors[key];
                if (Array.isArray(messages)) {
                    messages.forEach(function(message) {
                        errorMessages += '<li>' + message + '</li>';
                    });
                } else {
                    errorMessages += '<li>' + messages + '</li>';
                }
            });
            errorMessages += '</ul>';
        } else {
            errorMessages = 'An unknown error occurred.';
        }

        $('#alert').removeClass('alert-success').addClass('alert-danger');
        $('#alert-content').html(errorMessages);
        $('#alert').show();

        if ($('#alert').is(':visible')) {
            $('html, body').animate({
                scrollTop: $('#alert').offset().top
            }, 400);
        }
    }

    $('#alert').on('click', '.close', function() {
        $('#alert').hide();
    });
});
    </script>
@endpush
