@php
    $company = $company ?? null;
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="_method" id="company-form-method" value="{{ $method !== 'POST' ? $method : '' }}">
    <input type="hidden" name="_form_action" id="company-form-action" value="{{ $action }}">

    <div class="row g-3">
        <div class="col-md-4">
            <x-form.text name="code" label="Kode" :value="$company?->code" inputClass="form-control-sm" required maxlength="20" />
        </div>

        <div class="col-md-8">
            <x-form.text name="name" label="Nama" :value="$company?->name" inputClass="form-control-sm" required />
        </div>

        <div class="col-md-6">
            <x-form.input type="email" name="email" label="Email" :value="$company?->email" inputClass="form-control-sm" maxlength="150" />
        </div>

        <div class="col-md-6">
            <x-form.text name="phone" label="Telepon" :value="$company?->phone" inputClass="form-control-sm" maxlength="20" />
        </div>

        <div class="col-12">
            <x-form.file name="image" label="Gambar" mode="filepond" accept="image/*"
                :acceptedFileTypes="['image/jpeg', 'image/png', 'image/gif', 'image/webp']" maxFileSize="2MB"
                imagePreviewHeight="200" framePreviewHeight="200"
                :existingFiles="$company?->imageUrl() ? [$company->imageUrl()] : []" />
        </div>

        <div class="col-12">
            <x-form.textarea name="notes" label="Keterangan" :value="$company?->notes" rows="3" inputClass="form-control-sm" />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
