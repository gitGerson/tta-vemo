<?php

namespace App\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;

/**
 * Base class for a config-driven server-side DataTable.
 *
 * A single subclass is the source of truth for one table and is consumed by
 * BOTH the controller data endpoint (->response()) and the <x-datatable> view
 * component (headers/jsColumns/exportColumnIndexes). Columns are declared once
 * via columns() and never duplicated across header markup, JS config, and the
 * yajra response.
 */
abstract class DataTable
{
    /** Base Eloquent query (apply select / with / withCount here). */
    abstract public function query(): Builder;

    /** @return Column[] The single source of truth for this table's columns. */
    abstract public function columns(): array;

    /** DOM id of the <table> element. */
    abstract public function id(): string;

    /** Data endpoint URL the table fetches rows from. */
    abstract public function ajax(): string;

    /** Default sort, e.g. [[1, 'asc']]; null leaves the DataTables default. */
    public function order(): ?array
    {
        return null;
    }

    /** Route name used to make rows clickable; null disables clickable rows. */
    public function clickableRoute(): ?string
    {
        return null;
    }

    /** Row-data field carrying the navigation URL for clickable rows. */
    public function rowUrlField(): string
    {
        return 'row_url';
    }

    /**
     * DOM id of a detail modal. When set (and the table is clickable), a row
     * click / detail-modal action fetches row_url and loads the response HTML
     * into that modal instead of navigating to it.
     */
    public function detailModal(): ?string
    {
        return null;
    }

    public function hasDetailModal(): bool
    {
        return $this->detailModal() !== null;
    }

    /**
     * Action descriptors for one row, consumed by <x-datatable.actions>.
     * Override when the table declares Column::action().
     *
     * @return array<int, array<string, mixed>>
     */
    public function actions(mixed $row): array
    {
        return [];
    }

    public function isClickable(): bool
    {
        return $this->clickableRoute() !== null;
    }

    /** Build the yajra JSON response from the column definitions. */
    public function response(): JsonResponse
    {
        $dataTable = DataTables::of($this->query());
        $rawColumns = [];

        foreach ($this->columns() as $column) {
            if ($column->isIndex) {
                $dataTable->addIndexColumn();
            } elseif ($column->isAction) {
                $dataTable->addColumn('action', fn ($row): string => view('components.datatable.actions', [
                    'actions' => $this->actions($row),
                ])->render());
            } elseif ($column->renderUsing !== null) {
                $dataTable->addColumn($column->key, $column->renderUsing);
            }

            if ($column->raw) {
                $rawColumns[] = $column->key;
            }
        }

        if ($this->isClickable()) {
            $route = $this->clickableRoute();
            $dataTable->addColumn($this->rowUrlField(), fn ($row): string => route($route, $row));
        }

        if ($rawColumns !== []) {
            $dataTable->rawColumns($rawColumns);
        }

        return $dataTable->make(true);
    }

    /** Client-side column config array. */
    public function jsColumns(): array
    {
        return array_map(fn (Column $column): array => $column->toJs(), $this->columns());
    }

    /** Header labels for <thead>. */
    public function headers(): array
    {
        return array_map(fn (Column $column): string => $column->label, $this->columns());
    }

    /** Zero-based indexes of exportable columns (replaces positional exclusion). */
    public function exportColumnIndexes(): array
    {
        $indexes = [];

        foreach (array_values($this->columns()) as $index => $column) {
            if ($column->exportable) {
                $indexes[] = $index;
            }
        }

        return $indexes;
    }
}
