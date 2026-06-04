@extends('layouts.sneat')

@section('title')
    Daftar Kendaraan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $canManageReferences = auth()->user()->hasPermission('master.vehicle_types.view')
                || auth()->user()->hasPermission('master.vehicle_brands.view');
        @endphp

        <x-table :table="$table" engine="tabulator" layout="fitDataStretch" title="Daftar Kendaraan">
            <x-slot:actions>
                <div class="d-flex gap-2">
                    @if($canManageReferences)
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="vehicle-references" aria-expanded="false">
                                <i class="bx bx-cog me-1"></i> Referensi
                            </button>
                            <div class="dropdown-menu" id="vehicle-references-menu">
                                @if(auth()->user()->hasPermission('master.vehicle_types.view'))
                                    <a class="dropdown-item" href="{{ route('vehicle-types.index') }}"><i class="bx bx-category me-2"></i> Tipe Kendaraan</a>
                                @endif
                                @if(auth()->user()->hasPermission('master.vehicle_brands.view'))
                                    <a class="dropdown-item" href="{{ route('vehicle-brands.index') }}"><i class="bx bx-purchase-tag me-2"></i> Brand Kendaraan</a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->hasPermission('master.vehicles.delete'))
                        <a href="{{ route('vehicles.trash') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-trash me-1"></i> Trash
                        </a>
                    @endif

                    @if(auth()->user()->hasPermission('master.vehicles.create'))
                        <a href="{{ route('vehicles.create') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-plus me-1"></i> Tambah Kendaraan
                        </a>
                    @endif
                </div>
            </x-slot:actions>
        </x-table>
    </div>
@endsection

@push('scripts')
    <script>
        // Manual toggle — the app loads two Bootstrap bundles, so data-bs-toggle
        // fires the data-api twice and immediately re-closes the menu.
        document.addEventListener('DOMContentLoaded', () => {
            const button = document.getElementById('vehicle-references');
            const menu = document.getElementById('vehicle-references-menu');
            if (!button || !menu) return;

            const container = button.closest('.dropdown');

            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                const isOpen = menu.classList.toggle('show');
                button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            document.addEventListener('click', (event) => {
                if (!container.contains(event.target)) {
                    menu.classList.remove('show');
                    button.setAttribute('aria-expanded', 'false');
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    menu.classList.remove('show');
                    button.setAttribute('aria-expanded', 'false');
                }
            });
        });
    </script>
@endpush
