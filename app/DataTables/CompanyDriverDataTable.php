<?php

namespace App\DataTables;

use App\Models\Company;
use App\Models\Driver;
use Illuminate\Database\Eloquent\Builder;

class CompanyDriverDataTable extends DataTable
{
    public function __construct(private readonly Company $company) {}

    public function query(): Builder
    {
        return Driver::query()
            ->whereHas('companies', fn (Builder $query): Builder => $query->whereKey($this->company->getKey()))
            ->select([
                'id',
                'name',
                'nik',
                'no_identity',
                'residence',
                'date_birth',
                'no_phone_1',
                'entry_date',
                'out_date',
                'status',
            ]);
    }

    public function columns(): array
    {
        return [
            Column::index(),
            Column::make('name', 'Nama'),
            Column::make('nik', 'No Karyawan'),
            Column::make('no_identity', 'No Identitas'),
            Column::make('residence', 'Domisili'),
            Column::computed('date_birth_label', 'Tanggal Lahir', fn (Driver $driver): string => $driver->date_birth?->format('d M Y') ?? '-')
                ->searchable(false),
            Column::make('no_phone_1', 'No Telepon'),
            Column::computed('entry_date_label', 'Tanggal Masuk', fn (Driver $driver): string => $driver->entry_date?->format('d M Y') ?? '-')
                ->searchable(false),
            Column::make('status', 'Status'),
        ];
    }

    public function id(): string
    {
        return 'company-'.$this->company->getKey().'-drivers-table';
    }

    public function ajax(): string
    {
        return route('companies.drivers.data', $this->company);
    }
}
