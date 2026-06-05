<?php

namespace App\DataTables;

use Illuminate\Support\Collection;

/**
 * Client-side ("local") editable sub-grid for maintenance detail line items.
 *
 * Unlike the remote DataTables in this directory, this one is not query-backed:
 * rows are delivered inline via clientData() and the grid is rendered in local
 * mode by resources/js/modules/tabulator.js. Edits never hit a REST endpoint —
 * on parent-form submit they serialize into items[i][...] hidden inputs, which
 * MaintenanceController@store/@update already consume.
 */
class MaintenanceDetailDataTable extends DataTable
{
    /**
     * @param  array<int, string>  $typeNames  Maintenance type names for the select2 editor.
     * @param  array<int, array<string, mixed>>  $rows  Raw detail rows (empty on create).
     */
    public function __construct(
        private readonly array $typeNames = [],
        private readonly array $rows = [],
        private readonly bool $readOnly = false,
    ) {}

    public function query(): Collection
    {
        return new Collection;
    }

    public function columns(): array
    {
        $columns = [
            Column::make('maintenance_name', 'Nama Barang/Jasa')
                ->orderable(false)
                ->editable($this->readOnly ? false : 'select2', ['options' => $this->typeNames])
                ->formatter('nameOrPlaceholder', ['placeholder' => '- Pilih jenis -'])
                ->tabulator(['minWidth' => 200, 'widthGrow' => 3]),

            Column::make('price', 'Harga Satuan')
                ->orderable(false)
                ->editable($this->readOnly ? false : 'number', ['min' => 0, 'selectContents' => true])
                ->formatter('money', $this->moneyParams())
                ->tabulator(['hozAlign' => 'right', 'minWidth' => 130, 'widthGrow' => 2]),

            Column::make('qty', 'Jumlah')
                ->orderable(false)
                ->editable($this->readOnly ? false : 'number', ['min' => 1])
                ->tabulator(['hozAlign' => 'center', 'width' => 90]),

            Column::make('subtotal', 'Total')
                ->orderable(false)
                ->editable(false)
                ->formatter('moneyProduct', ['fields' => ['price', 'qty']] + $this->moneyParams())
                ->tabulator(['hozAlign' => 'right', 'minWidth' => 130, 'widthGrow' => 2, 'headerSort' => false]),
        ];

        if (! $this->readOnly) {
            $columns[] = Column::make('actions', 'Aksi')
                ->orderable(false)
                ->searchable(false)
                ->notExportable()
                ->formatter('rowDelete')
                ->tabulator(['width' => 70, 'hozAlign' => 'center', 'headerSort' => false, 'resizable' => false]);
        }

        return $columns;
    }

    public function id(): string
    {
        return 'maintenance-detail';
    }

    public function ajax(): string
    {
        return '';
    }

    public function mode(): string
    {
        return 'local';
    }

    public function editable(): bool
    {
        return ! $this->readOnly;
    }

    public function clientData(): array
    {
        return $this->rows;
    }

    public function formField(): ?string
    {
        return 'items';
    }

    public function formTarget(): ?string
    {
        return '#mainForm';
    }

    public function formFieldMap(): array
    {
        return [
            'maintenance_name' => 'maintenance_name',
            'price' => 'price',
            'qty' => 'qty',
        ];
    }

    public function addRowDefaults(): array
    {
        return ['maintenance_name' => '', 'price' => 0, 'qty' => 1];
    }

    public function addRowFocusField(): ?string
    {
        return 'maintenance_name';
    }

    /**
     * @return array<string, mixed>
     */
    private function moneyParams(): array
    {
        return ['symbol' => 'Rp ', 'thousand' => '.', 'decimal' => ',', 'precision' => 0];
    }
}
