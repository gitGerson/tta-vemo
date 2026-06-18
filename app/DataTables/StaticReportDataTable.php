<?php

namespace App\DataTables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Base for a read-only, client-side (local) report table rendered by
 * <x-table engine="tabulator">.
 *
 * Unlike the server-side DataTable subclasses, report tables hold pre-computed
 * rows (aggregations / pivots) and define grouped headers + footer calcs that
 * the Column abstraction cannot express, so concrete reports override
 * tabulatorColumns() with raw Tabulator column defs and clientData() with the
 * shaped rows. Local mode + no create/update/delete URLs + editable() === false
 * means the rendered table has no Aksi column and no Add/Save/Delete buttons.
 */
abstract class StaticReportDataTable extends DataTable
{
    /** Local (client-side) mode: rows come from clientData(), not an ajax endpoint. */
    public function mode(): string
    {
        return 'local';
    }

    /** Report tables are presentational; no DB query backs them. */
    public function query(): Builder|Collection
    {
        return new Collection;
    }

    /** Columns are declared via tabulatorColumns(); the Column[] path is unused. */
    public function columns(): array
    {
        return [];
    }

    /** Local mode reads inline data, so there is no data endpoint. */
    public function ajax(): string
    {
        return '';
    }
}
