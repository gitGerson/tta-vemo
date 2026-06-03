<?php

namespace App\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Yajra\DataTables\Facades\DataTables;

/**
 * Base class for a config-driven server-side DataTable.
 *
 * A single subclass is the source of truth for one table and is consumed by
 * BOTH the controller data endpoint (->response()) and the <x-table> /
 * <x-datatable> view component (headers/jsColumns/exportColumnIndexes). Columns
 * are declared once via columns() and never duplicated across header markup, JS
 * config, and the yajra response. The same definition drives any frontend
 * engine — jQuery DataTables (default) or Tabulator.
 */
abstract class DataTable
{
    /**
     * Data source for the table. Return an Eloquent query (apply select / with /
     * withCount here) for server-side DB tables, or a Collection of rows for
     * static/in-memory tables. Yajra picks the matching engine automatically.
     */
    abstract public function query(): Builder|Collection;

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

    public function rowKeyField(): string
    {
        return 'id';
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

    public function tabulatorCreateUrl(): ?string
    {
        return null;
    }

    public function tabulatorUpdateUrl(): ?string
    {
        return null;
    }

    public function tabulatorDeleteUrl(): ?string
    {
        return null;
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

    /** Build the correct JSON response for the requested table engine. */
    public function responseFor(Request $request): JsonResponse
    {
        if ($request->string('engine')->toString() === 'tabulator') {
            return $this->tabulatorResponse($request);
        }

        return $this->response();
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

    /** Build the JSON response expected by Tabulator remote pagination. */
    public function tabulatorResponse(Request $request): JsonResponse
    {
        $size = max(1, min((int) $request->integer('size', 10), 100));
        $page = max(1, (int) $request->integer('page', 1));
        $source = $this->query();

        if ($source instanceof Builder) {
            $query = clone $source;
            $sorters = $this->normalizedTabulatorSorters($request->input('sort', []));

            $this->applyTabulatorFilters($query, $request->input('filter', []));
            $this->applyTabulatorSort($query, $sorters);

            if ($sorters === [] && $this->order() !== null) {
                $this->applyDefaultOrder($query);
            }

            $total = (clone $query)->toBase()->getCountForPagination();
            $rows = $query->forPage($page, $size)->get();

            return response()->json([
                'data' => $this->rows($rows, ($page - 1) * $size),
                'last_page' => max(1, (int) ceil($total / $size)),
                'last_row' => $total,
            ]);
        }

        $rows = $this->filterCollection($source, $request->input('filter', []));
        $rows = $this->sortCollection($rows, $request->input('sort', []));
        $total = $rows->count();

        return response()->json([
            'data' => $this->rows($rows->forPage($page, $size)->values(), ($page - 1) * $size),
            'last_page' => max(1, (int) ceil($total / $size)),
            'last_row' => $total,
        ]);
    }

    /** Client-side column config array for jQuery DataTables. */
    public function jsColumns(): array
    {
        return array_map(fn (Column $column): array => $column->toJs(), $this->columns());
    }

    /** Column config array for the Tabulator engine. */
    public function tabulatorColumns(): array
    {
        return array_map(fn (Column $column): array => $column->toTabulator(), $this->columns());
    }

    /**
     * Default sort translated to Tabulator's initialSort format.
     *
     * Maps the column-index based order() (DataTables convention) to Tabulator's
     * field-based {column, dir} descriptors.
     *
     * @return array<int, array{column: string, dir: string}>
     */
    public function tabulatorInitialSort(): array
    {
        if ($this->order() === null) {
            return [];
        }

        $columns = array_values($this->columns());
        $sort = [];

        foreach ($this->order() as [$index, $direction]) {
            if (isset($columns[$index])) {
                $sort[] = ['column' => $columns[$index]->key, 'dir' => $direction];
            }
        }

        return $sort;
    }

    /**
     * Fully rendered rows for client-side engines (no server-side protocol).
     *
     * Applies the same column definitions used by response(): the index counter,
     * computed/render closures, raw action HTML, and the clickable row_url. The
     * result is a plain array any client-side library can consume directly.
     *
     * @return array<int, array<string, mixed>>
     */
    public function data(): array
    {
        $source = $this->query();
        $rows = $source instanceof Builder ? $source->get() : $source;

        return $this->rows($rows);
    }

    public function rowData(mixed $row): array
    {
        return $this->rows(new Collection([$row]))[0];
    }

    /**
     * Fully rendered row array for client libraries.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function rows(Collection $rows, int $offset = 0): array
    {
        $clickable = $this->isClickable();
        $route = $this->clickableRoute();
        $position = $offset;

        return $rows->map(function (mixed $row) use (&$position, $clickable, $route): array {
            $position++;
            $record = [];

            foreach ($this->columns() as $column) {
                if ($column->isIndex) {
                    $record[$column->key] = $position;
                } elseif ($column->isAction) {
                    $record[$column->key] = view('components.datatable.actions', [
                        'actions' => $this->actions($row),
                    ])->render();
                } elseif ($column->renderUsing !== null) {
                    $record[$column->key] = ($column->renderUsing)($row);
                } else {
                    $record[$column->key] = data_get($row, $column->key);
                }
            }

            $record[$this->rowKeyField()] = data_get($row, $this->rowKeyField());

            if ($clickable) {
                $record[$this->rowUrlField()] = route($route, $row);
            }

            return $record;
        })->values()->all();
    }

    protected function applyTabulatorFilters(Builder $query, mixed $filters): void
    {
        foreach ($this->normalizedTabulatorFilters($filters) as $filter) {
            if ($filter['field'] === '__global') {
                $this->applyGlobalTabulatorFilter($query, $filter['value']);

                continue;
            }

            $column = $this->columnByKey($filter['field']);

            if ($column === null || ! $column->searchable || $column->renderUsing !== null || $column->isIndex || $column->isAction) {
                continue;
            }

            $value = $filter['value'];

            if ($value === null || $value === '') {
                continue;
            }

            match ($filter['type']) {
                '=', 'match' => $query->where($column->key, $value),
                default => $query->where($column->key, 'like', '%'.$value.'%'),
            };
        }
    }

    protected function applyGlobalTabulatorFilter(Builder $query, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $searchableColumns = array_filter(
            $this->columns(),
            fn (Column $column): bool => $column->searchable
                && $column->renderUsing === null
                && ! $column->isIndex
                && ! $column->isAction
                && ! str_contains($column->key, '.')
        );

        if ($searchableColumns === []) {
            return;
        }

        $query->where(function (Builder $query) use ($searchableColumns, $value): void {
            foreach ($searchableColumns as $column) {
                $query->orWhere($column->key, 'like', '%'.$value.'%');
            }
        });
    }

    protected function applyTabulatorSort(Builder $query, mixed $sorters): void
    {
        foreach ($this->normalizedTabulatorSorters($sorters) as $sorter) {
            $column = $this->columnByKey($sorter['field']);

            if ($column === null || ! $column->orderable || $column->renderUsing !== null || $column->isIndex || $column->isAction) {
                continue;
            }

            $query->orderBy($column->key, $sorter['dir']);
        }
    }

    protected function applyDefaultOrder(Builder $query): void
    {
        $columns = array_values($this->columns());

        foreach ($this->order() ?? [] as [$index, $direction]) {
            $column = $columns[$index] ?? null;

            if ($column !== null && $column->orderable && $column->renderUsing === null && ! $column->isIndex && ! $column->isAction) {
                $query->orderBy($column->key, $direction);
            }
        }
    }

    protected function filterCollection(Collection $rows, mixed $filters): Collection
    {
        foreach ($this->normalizedTabulatorFilters($filters) as $filter) {
            if ($filter['field'] === '__global') {
                $rows = $this->filterCollectionGlobally($rows, $filter['value']);

                continue;
            }

            $column = $this->columnByKey($filter['field']);

            if ($column === null || ! $column->searchable || $column->isIndex || $column->isAction) {
                continue;
            }

            $value = (string) $filter['value'];

            if ($value === '') {
                continue;
            }

            $rows = $rows->filter(function (mixed $row) use ($column, $filter, $value): bool {
                $fieldValue = $column->renderUsing !== null
                    ? (string) ($column->renderUsing)($row)
                    : (string) data_get($row, $column->key);

                return match ($filter['type']) {
                    '=', 'match' => $fieldValue === $value,
                    default => str_contains(strtolower($fieldValue), strtolower($value)),
                };
            });
        }

        return $rows->values();
    }

    protected function filterCollectionGlobally(Collection $rows, mixed $value): Collection
    {
        $value = (string) $value;

        if ($value === '') {
            return $rows;
        }

        $columns = array_filter(
            $this->columns(),
            fn (Column $column): bool => $column->searchable && ! $column->isIndex && ! $column->isAction
        );

        return $rows->filter(function (mixed $row) use ($columns, $value): bool {
            foreach ($columns as $column) {
                $fieldValue = $column->renderUsing !== null
                    ? (string) ($column->renderUsing)($row)
                    : (string) data_get($row, $column->key);

                if (str_contains(strtolower($fieldValue), strtolower($value))) {
                    return true;
                }
            }

            return false;
        })->values();
    }

    protected function sortCollection(Collection $rows, mixed $sorters): Collection
    {
        foreach (array_reverse($this->normalizedTabulatorSorters($sorters)) as $sorter) {
            $column = $this->columnByKey($sorter['field']);

            if ($column === null || ! $column->orderable || $column->isIndex || $column->isAction) {
                continue;
            }

            $rows = $rows->sortBy(
                fn (mixed $row): mixed => $column->renderUsing !== null ? ($column->renderUsing)($row) : data_get($row, $column->key),
                SORT_REGULAR,
                $sorter['dir'] === 'desc'
            );
        }

        return $rows->values();
    }

    protected function columnByKey(string $key): ?Column
    {
        foreach ($this->columns() as $column) {
            if ($column->key === $key) {
                return $column;
            }
        }

        return null;
    }

    /** @return array<int, array{field: string, type: string, value: mixed}> */
    protected function normalizedTabulatorFilters(mixed $filters): array
    {
        if (! is_array($filters)) {
            return [];
        }

        return collect($filters)
            ->filter(fn (mixed $filter): bool => is_array($filter) && isset($filter['field'], $filter['value']))
            ->map(fn (array $filter): array => [
                'field' => (string) $filter['field'],
                'type' => (string) ($filter['type'] ?? 'like'),
                'value' => $filter['value'],
            ])
            ->values()
            ->all();
    }

    /** @return array<int, array{field: string, dir: string}> */
    protected function normalizedTabulatorSorters(mixed $sorters): array
    {
        if (! is_array($sorters)) {
            return [];
        }

        return collect($sorters)
            ->filter(fn (mixed $sorter): bool => is_array($sorter) && isset($sorter['field']))
            ->map(fn (array $sorter): array => [
                'field' => (string) $sorter['field'],
                'dir' => ($sorter['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc',
            ])
            ->values()
            ->all();
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
