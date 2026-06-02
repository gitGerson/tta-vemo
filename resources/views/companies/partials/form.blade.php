@php
    $company = $company ?? null;
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    <input type="hidden" name="_method" id="company-form-method" value="{{ $method !== 'POST' ? $method : '' }}">
    <input type="hidden" name="_form_action" id="company-form-action" value="{{ $action }}">

    <div class="row g-3">
        <div class="col-md-4">
            <x-form.text name="code" label="Kode" :value="$company?->code" inputClass="form-control-sm" required maxlength="6" />
        </div>

        <div class="col-md-8">
            <x-form.text name="name" label="Nama" :value="$company?->name" inputClass="form-control-sm" required />
        </div>

        <div class="col-12">
            <x-form.textarea name="desc" label="Keterangan" :value="$company?->desc" rows="3" inputClass="form-control-sm" />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
