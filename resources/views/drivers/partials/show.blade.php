@php
    $statusClass = $driver->status === 'ACTIVE' ? 'bg-label-success text-success' : 'bg-label-secondary text-secondary';
    $companies = $driver->companies;
@endphp

<style>
    .driver-employee-card {
        --driver-card-border: var(--bs-border-color);
        --driver-card-muted: var(--bs-secondary-color);
    }

    .driver-employee-card__photo {
        width: 132px;
        height: 150px;
        object-fit: cover;
        background: var(--bs-body-bg);
    }

    .driver-employee-card__panel {
        border: 1px solid var(--driver-card-border);
        border-radius: .5rem;
        background: var(--bs-body-bg);
    }

    .driver-employee-card__label {
        color: var(--driver-card-muted);
        font-size: .75rem;
        font-weight: 600;
    }

    .driver-employee-card__value {
        color: var(--bs-body-color);
        font-weight: 600;
        line-height: 1.35;
    }
</style>

<div class="driver-employee-card d-flex flex-column gap-3">
    <div class="driver-employee-card__panel p-3">
        <div class="d-flex flex-column flex-sm-row gap-3 align-items-sm-center">
            <div class="flex-shrink-0">
                <img
                    src="{{ $driver->imageUrl() ?? asset('assets/driver_avatar.png') }}"
                    alt="{{ $driver->name }}"
                    class="driver-employee-card__photo rounded border"
                >
            </div>

            <div class="flex-grow-1 min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h4 class="mb-0 text-truncate">{{ $driver->name }}</h4>
                    <span class="badge {{ $statusClass }}">{{ $driver->status }}</span>
                </div>

                <div class="text-muted fw-semibold mb-3">{{ $driver->nik ?: '-' }}</div>

                <div class="d-flex flex-wrap gap-2">
                    @forelse($companies as $company)
                        <span class="badge bg-label-primary">{{ $company->name }} ({{ $company->code ?: '-' }})</span>
                    @empty
                        <span class="badge bg-label-secondary">Tanpa perusahaan</span>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="driver-employee-card__panel p-3 h-100">
                <div class="text-uppercase text-muted small fw-semibold mb-3">Identitas</div>
                <div class="d-grid gap-3">
                    <div>
                        <div class="driver-employee-card__label">No Identitas</div>
                        <div class="driver-employee-card__value">{{ $driver->no_identity }}</div>
                    </div>
                    <div>
                        <div class="driver-employee-card__label">Tempat / Tanggal Lahir</div>
                        <div class="driver-employee-card__value">
                            {{ $driver->residence }}{{ $driver->date_birth ? ', '.$driver->date_birth->format('d M Y') : '' }}
                        </div>
                    </div>
                    <div>
                        <div class="driver-employee-card__label">Alamat</div>
                        <div class="driver-employee-card__value">{{ $driver->address }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="driver-employee-card__panel p-3 h-100">
                <div class="text-uppercase text-muted small fw-semibold mb-3">Kontak & Pekerjaan</div>
                <div class="d-grid gap-3">
                    <div>
                        <div class="driver-employee-card__label">Nomor Telepon</div>
                        <div class="driver-employee-card__value">{{ $driver->no_phone_1 }}</div>
                        <div class="text-muted">{{ $driver->no_phone_2 ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="driver-employee-card__label">Tanggal Masuk</div>
                        <div class="driver-employee-card__value">{{ $driver->entry_date?->format('d M Y') ?: '-' }}</div>
                    </div>
                    <div>
                        <div class="driver-employee-card__label">Tanggal Keluar</div>
                        <div class="driver-employee-card__value">{{ $driver->out_date?->format('d M Y') ?: '-' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
