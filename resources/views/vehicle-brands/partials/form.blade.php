@php
    $vehicleBrand = $vehicleBrand ?? null;
@endphp

<form method="POST" action="{{ $action }}">
    @csrf
    <input type="hidden" name="_method" id="vehicle-brand-form-method" value="{{ $method !== 'POST' ? $method : '' }}">
    <input type="hidden" name="_form_action" id="vehicle-brand-form-action" value="{{ $action }}">

    <div class="row g-3">
        <div class="col-12">
            <x-form.text name="name" label="Nama" :value="$vehicleBrand?->name" inputClass="form-control-sm" required maxlength="255" />
        </div>

        <div class="col-12">
            <x-form.file name="logo" label="Logo" mode="filepond" accept="image/*"
                :acceptedFileTypes="['image/jpeg', 'image/png', 'image/gif', 'image/webp']" maxFileSize="2MB"
                :existingFiles="array_filter([$vehicleBrand?->logoUrl()])" imagePreviewHeight="140" framePreviewHeight="160" />
        </div>

        <div class="col-12">
            <x-form.textarea name="descriptions" label="Deskripsi" :value="$vehicleBrand?->descriptions" rows="3" inputClass="form-control-sm" />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
