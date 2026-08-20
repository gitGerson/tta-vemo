@php
    $destination = $destination ?? null;
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    <input type="hidden" name="_method" id="destination-form-method" value="{{ $method !== 'POST' ? $method : '' }}">
    <input type="hidden" name="_form_action" id="destination-form-action" value="{{ $action }}">

    <div class="row g-3">
        <div class="col-12">
            <x-form.text name="destination" label="Tujuan" :value="$destination?->destination" inputClass="form-control-sm" required maxlength="50" />
        </div>

        <div class="col-12">
            <x-form.textarea name="desc" label="Keterangan" :value="$destination?->desc" rows="3" inputClass="form-control-sm" />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
