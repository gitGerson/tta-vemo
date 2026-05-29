@php
    $driver = $driver ?? null;
    $statusOptions = [
        'ACTIVE' => 'ACTIVE',
        'PASSIVE' => 'PASSIVE',
    ];
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="row g-3">
        <div class="col-md-6">
            <x-form.file
                name="image"
                label="Foto"
                accept="image/*"
            />
        </div>

        <div class="col-md-6">
            <x-form.text
                name="nik"
                label="NIK Karyawan"
                :value="$driver?->nik"
                required
            />
        </div>

        <div class="col-md-6">
            <x-form.text
                name="name"
                label="Nama"
                :value="$driver?->name"
                required
            />
        </div>

        <div class="col-md-6">
            <x-form.text
                name="no_identity"
                label="No Identitas"
                :value="$driver?->no_identity"
                required
            />
        </div>

        <div class="col-12">
            <x-form.textarea
                name="address"
                label="Alamat"
                rows="2"
                :value="$driver?->address"
                required
            />
        </div>

        <div class="col-md-6">
            <x-form.text
                name="residence"
                label="Tempat"
                :value="$driver?->residence"
                required
            />
        </div>

        <div class="col-md-6">
            <x-form.date
                name="date_birth"
                label="Tanggal Lahir"
                :value="$driver?->date_birth?->format('Y-m-d')"
                required
            />
        </div>

        <div class="col-md-6">
            <x-form.text
                name="no_phone_1"
                label="HP"
                :value="$driver?->no_phone_1"
                placeholder="08xxxx"
                required
            />
        </div>

        <div class="col-md-6">
            <x-form.text
                name="no_phone_2"
                label="HP Wali"
                :value="$driver?->no_phone_2"
                placeholder="08xxxx"
                required
            />
        </div>

        <div class="col-md-6">
            <x-form.date
                name="entry_date"
                label="Tanggal Masuk"
                :value="$driver?->entry_date?->format('Y-m-d')"
                required
            />
        </div>

        <div class="col-md-6">
            <x-form.date
                name="out_date"
                label="Tanggal Keluar"
                :value="$driver?->out_date?->format('Y-m-d')"
            />
        </div>

        <div class="col-md-6">
            <x-form.select
                name="status"
                label="Status"
                :options="$statusOptions"
                :value="$driver?->status ?? 'ACTIVE'"
                required
            />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('drivers.index') }}" class="btn btn-outline-secondary">Batal</a>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>
