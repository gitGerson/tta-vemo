@extends('layouts.sneat')

@section('title')
    Upload Foto Kendaraan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">{{ $vehicle !== null ? 'Upload Foto: ' . $vehicle->plat_new : 'Upload Foto Kendaraan' }}</h4>
            <a href="{{ route('vehicles.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i> Kembali
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @include('vehicle-photos.partials.form', [
                    'vehicle' => $vehicle,
                    'vehicles' => $vehicles,
                    'selectedVehicleId' => $selectedVehicleId,
                    'periodsByVehicle' => $periodsByVehicle,
                    'documentedPeriods' => $documentedPeriods,
                    'action' => route('vehicle-photos.store'),
                    'method' => 'POST',
                    'submitLabel' => 'Simpan',
                ])
            </div>
        </div>

        @if(auth()->user()->hasPermission('operational.vehicle_photos.view'))
            <div class="mt-4">
                {{-- fitColumns, not fitDataStretch: content-sized columns can overflow
                     the container, and the resulting horizontal scrollbar toggles the
                     page's vertical one, which narrows the viewport and re-triggers the
                     layout — the scrollbars oscillate in a narrow band of widths. --}}
                <x-table :table="$table" engine="tabulator" layout="fitColumns" title="Foto Kendaraan Terunggah" />
            </div>
        @endif
    </div>
@endsection

@if(auth()->user()->hasPermission('operational.vehicle_photos.view'))
    @push('scripts')
        <script>
            // Filter the photo table by the form's selected vehicle. The Tabulator
            // instance is exposed at el.tabulatorInstance and reads options.ajaxParams
            // on every remote request, so mutating it + setData() re-filters and the
            // filter persists across pagination/sort.
            (function () {
                const tableEl = document.getElementById('vehicle-photos-table-tabulator');
                if (!tableEl) return;

                const applyFilter = function () {
                    const t = tableEl.tabulatorInstance;
                    if (!t) return false;

                    const id = (window.jQuery
                        ? window.jQuery('#vehicle_id').val()
                        : (document.getElementById('vehicle_id') || {}).value) || '';

                    t.options.ajaxParams = id
                        ? { engine: 'tabulator', vehicle_id: id }
                        : { engine: 'tabulator' };
                    t.setData();
                    return true;
                };

                // The instance is created by the deferred tabulator.js module, which may
                // run after this script — poll briefly until it exists.
                (function waitForTable(tries) {
                    if (applyFilter() || tries <= 0) return;
                    setTimeout(function () { waitForTable(tries - 1); }, 100);
                })(30);

                // Select2 fires its change through jQuery, matching the period script.
                if (window.jQuery) {
                    window.jQuery(document).on('change', '#vehicle_id', applyFilter);
                } else {
                    const sel = document.getElementById('vehicle_id');
                    if (sel) sel.addEventListener('change', applyFilter);
                }
            })();
        </script>
    @endpush
@endif
