@php
    $driver = $driver ?? null;
    $statusOptions = [
        'ACTIVE' => 'ACTIVE',
        'PASSIVE' => 'PASSIVE',
    ];
    $selectedCompanyIds = collect(old('company_ids', $driver?->companies?->pluck('id')->all() ?? []))
        ->map(fn ($id) => (string) $id)
        ->all();
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="_method" id="driver-form-method" value="{{ $method !== 'POST' ? $method : '' }}">
    <input type="hidden" name="_form_action" id="driver-form-action" value="{{ $action }}">

    <div class="row g-3">
        {{-- NIK first: user enters this to trigger employee prefill --}}
        <div class="col-md-12">
            <x-form.group name="nik" label="Nomor Karyawan">
                <div class="input-group">
                    <input type="text" id="nik" name="nik" class="form-control form-control-sm @error('nik') is-invalid @enderror"
                        value="{{ old('nik', $driver?->nik) }}" placeholder="Contoh: TTI0001938" />
                    <button type="button" class="btn btn-sm btn-primary" id="btn-search-nik">
                        <i class="bx bx-search"></i> Cari
                    </button>
                </div>
            </x-form.group>
        </div>

        <div class="col-12"></div>

        {{-- Photo beside prefilled identity fields --}}
        <div class="col-md-3">
            <x-form.file name="image" label="Foto" mode="filepond" accept="image/*" :acceptedFileTypes="['image/jpeg', 'image/png', 'image/gif', 'image/webp']" maxFileSize="2MB" imagePreviewHeight="200" framePreviewHeight="200"
                :existingFiles="$driver?->imageUrl() ? [$driver->imageUrl()] : []" />
        </div>

        <div class="col-md-9">
            <div class="row g-3">
                <div class="col-md-6">
                    <x-form.text name="name" label="Nama" :value="$driver?->name" inputClass="form-control-sm" required />
                </div>

                <div class="col-md-6">
                    <x-form.text name="no_identity" label="Nomor Identitas Kependudukan" :value="$driver?->no_identity"
                        inputClass="form-control-sm" required />
                </div>

                <div class="col-md-6">
                    <x-form.text name="residence" label="Tempat Lahir" :value="$driver?->residence" inputClass="form-control-sm" required />
                </div>

                <div class="col-md-6">
                    <x-form.date name="date_birth" label="Tanggal Lahir" :value="$driver?->date_birth?->format('Y-m-d')"
                        inputClass="form-control-sm" required />
                </div>

                <div class="col-md-6">
                    <x-form.text name="no_phone_1" label="HP" :value="$driver?->no_phone_1" placeholder="08xxxx"
                        inputClass="form-control-sm" required />
                </div>

                <div class="col-md-6">
                    <x-form.text name="no_phone_2" label="HP Wali" :value="$driver?->no_phone_2" placeholder="08xxxx"
                        inputClass="form-control-sm" required />
                </div>

                {{-- Employment --}}
                <div class="col-md-4">
                    <x-form.date name="entry_date" label="Tanggal Masuk" :value="$driver?->entry_date?->format('Y-m-d')"
                        inputClass="form-control-sm" required />
                </div>

                <div class="col-md-4">
                    <x-form.date name="out_date" label="Tanggal Keluar" :value="$driver?->out_date?->format('Y-m-d')" inputClass="form-control-sm" />
                </div>

                <div class="col-md-4">
                    <x-form.select name="status" label="Status" :options="$statusOptions" :value="$driver?->status ?? 'ACTIVE'" inputClass="form-select-sm" required />
                </div>
            </div>
        </div>
                <div class="col-md-12">
                    <x-form.select
                        name="company_ids"
                        label="Perusahaan"
                        mode="select2"
                        multiple
                        placeholder="Pilih perusahaan..."
                        :inputClass="$errors->has('company_ids.*') ? 'form-select-sm is-invalid' : 'form-select-sm'"
                    >
                        @foreach(($companies ?? collect()) as $company)
                            <option value="{{ $company->id }}" @selected(in_array((string) $company->id, $selectedCompanyIds, true))>
                                {{ $company->name }} ({{ $company->code }})
                            </option>
                        @endforeach
                    </x-form.select>
                    <x-form.error name="company_ids.*" />
                </div>

        {{-- Address full width --}}
        <div class="col-12">
            <x-form.textarea name="address" label="Alamat" rows="2" :value="$driver?->address" inputClass="form-control-sm" required />
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>
</form>

@push('scripts')
    <script>
        (function () {
            const btn = document.getElementById('btn-search-nik');
            if (!btn) return;

            const empInfoUrl = @json(route('drivers.empInfo'));

            btn.addEventListener('click', async function () {
                const nik = document.getElementById('nik').value.trim();
                if (!nik) return;

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Mencari...';

                try {
                    const res = await fetch(`${empInfoUrl}?nik=${encodeURIComponent(nik)}`, {
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    const result = await res.json();

                    if (result.success && result.data && result.data.length > 0) {
                        prefill(result.data[0]);
                    } else {
                        alert(result.message || 'Data karyawan tidak ditemukan.');
                    }
                } catch {
                    alert('Gagal menghubungi server. Coba lagi.');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bx bx-search"></i> Cari';
                }
            });

            function prefill(emp) {
                set('name', emp.Name ?? '');
                set('no_identity', emp.IdCardNumber ?? '');
                set('residence', emp.BirthPlace ?? '');
                set('date_birth', toDateInput(emp.BirthDate));
                set('address', emp.IDAddress ?? '');
                set('no_phone_1', normalizePhone(emp.mobilePhone));
                set('entry_date', toDateInput(emp.JoinDate));

                const statusEl = document.querySelector('[name="status"]');
                if (statusEl && emp.Status) {
                    statusEl.value = emp.Status.toUpperCase() === 'ACTIVE' ? 'ACTIVE' : 'PASSIVE';
                }
            }

            function set(name, value) {
                const el = document.querySelector(`[name="${name}"]`);
                if (el) el.value = value;
            }

            // "11/05/1994" (DD/MM/YYYY) → "1994-05-11" (YYYY-MM-DD)
            function toDateInput(str) {
                if (!str) return '';
                const [d, m, y] = str.split('/');
                return (y && m && d) ? `${y}-${m}-${d}` : '';
            }

            // "6285972844117" → "085972844117"
            function normalizePhone(phone) {
                if (!phone) return '';
                const s = String(phone).trim();
                return s.startsWith('62') ? '0' + s.slice(2) : s;
            }
        })();
    </script>
@endpush
