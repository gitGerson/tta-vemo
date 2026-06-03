@extends('layouts.sneat')

@section('title')
    Daftar Tujuan
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-datatable :table="$table" title="Daftar Tujuan">
            <x-slot:actions>
                <span class="badge bg-label-info">Preview data kota Laravolt Indonesia</span>
            </x-slot:actions>
        </x-datatable>
    </div>

    <div class="modal fade" id="destination-detail-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Tujuan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    {{-- AJAX-loaded detail fragment --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    document.dispatchEvent(new Event('form:enhance'));
})();
</script>
@endpush
