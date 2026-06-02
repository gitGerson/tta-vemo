@php
    $typeMaintenance = $typeMaintenance ?? null;
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    <input type="hidden" name="_method" id="type-maintenance-form-method" value="{{ $method !== 'POST' ? $method : '' }}">
    <input type="hidden" name="_form_action" id="type-maintenance-form-action" value="{{ $action }}">

    <div class="row g-3">
        <div class="col-12">
            <x-form.text name="name" label="Nama" :value="$typeMaintenance?->name" inputClass="form-control-sm" required />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
