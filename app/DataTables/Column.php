<?php

namespace App\DataTables;

use Closure;

/**
 * Immutable-ish descriptor for a single DataTable column.
 *
 * One Column definition is the single source of truth for: the <thead> header
 * label, the client-side JS column config, whether the value is server-rendered
 * (computed), whether it is a raw HTML column, and whether it is exportable.
 */
class Column
{
    public bool $orderable = true;

    public bool $searchable = true;

    public bool $exportable = true;

    public bool $raw = false;

    public bool $isIndex = false;

    public bool $isAction = false;

    public ?string $className = null;

    public ?Closure $renderUsing = null;

    public string|bool|null $tabulatorEditor = null;

    public ?array $tabulatorEditorParams = null;

    public ?string $tabulatorFormatter = null;

    public ?array $tabulatorFormatterParams = null;

    /** Arbitrary extra Tabulator column-def keys merged verbatim into toTabulator(). */
    public ?array $tabulatorExtra = null;

    private function __construct(
        public string $key,
        public string $label,
    ) {}

    /** A plain DB-backed column. */
    public static function make(string $key, string $label): self
    {
        return new self($key, $label);
    }

    /** The DT_RowIndex "No" counter column (maps to addIndexColumn()). */
    public static function index(string $label = 'No'): self
    {
        $column = new self('DT_RowIndex', $label);
        $column->isIndex = true;
        $column->orderable = false;
        $column->searchable = false;
        $column->exportable = false;

        return $column;
    }

    /** A server-rendered value column: fn($row): string. */
    public static function computed(string $key, string $label, Closure $renderUsing): self
    {
        $column = self::make($key, $label);
        $column->renderUsing = $renderUsing;

        return $column;
    }

    /** The raw HTML action column. */
    public static function action(string $label = 'Aksi'): self
    {
        $column = new self('action', $label);
        $column->isAction = true;
        $column->raw = true;
        $column->orderable = false;
        $column->searchable = false;
        $column->exportable = false;

        return $column;
    }

    public function orderable(bool $orderable = true): self
    {
        $this->orderable = $orderable;

        return $this;
    }

    public function searchable(bool $searchable = true): self
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function className(string $className): self
    {
        $this->className = $className;

        return $this;
    }

    /** Mark the column as raw HTML (added to rawColumns()). */
    public function raw(bool $raw = true): self
    {
        $this->raw = $raw;

        return $this;
    }

    public function notExportable(): self
    {
        $this->exportable = false;

        return $this;
    }

    public function render(Closure $renderUsing): self
    {
        $this->renderUsing = $renderUsing;

        return $this;
    }

    public function editable(string|bool $editor = 'input', ?array $editorParams = null): self
    {
        $this->tabulatorEditor = $editor;
        $this->tabulatorEditorParams = $editorParams;

        return $this;
    }

    public function formatter(string $formatter, ?array $formatterParams = null): self
    {
        $this->tabulatorFormatter = $formatter;
        $this->tabulatorFormatterParams = $formatterParams;

        return $this;
    }

    /** Merge arbitrary extra keys (hozAlign, width, minWidth, widthGrow, …) into the Tabulator def. */
    public function tabulator(array $extra): self
    {
        $this->tabulatorExtra = $extra;

        return $this;
    }

    /** Client-side column config object for jQuery DataTables. */
    public function toJs(): array
    {
        $config = [
            'data' => $this->key,
            'name' => $this->key,
            'orderable' => $this->orderable,
            'searchable' => $this->searchable,
        ];

        if ($this->className !== null) {
            $config['className'] = $this->className;
        }

        return $config;
    }

    /** Column definition for the Tabulator engine. */
    public function toTabulator(): array
    {
        $config = [
            'title' => $this->label,
            'field' => $this->key,
            'headerSort' => $this->orderable && ! $this->raw,
            'responsive' => $this->tabulatorResponsivePriority(),
        ];

        if ($this->className !== null) {
            $config['cssClass'] = $this->className;
        }

        if ($this->tabulatorEditor !== null) {
            $config['editor'] = $this->tabulatorEditor;

            if ($this->tabulatorEditorParams !== null) {
                $config['editorParams'] = $this->tabulatorEditorParams;
            }
        }

        if ($this->tabulatorFormatter !== null) {
            $config['formatter'] = $this->tabulatorFormatter;

            if ($this->tabulatorFormatterParams !== null) {
                $config['formatterParams'] = $this->tabulatorFormatterParams;
            }
        }

        if ($this->raw) {
            $config['formatter'] = 'html';
            $config['download'] = false;
        }

        if ($this->tabulatorExtra !== null) {
            $config = array_merge($config, $this->tabulatorExtra);
        }

        return $config;
    }

    private function tabulatorResponsivePriority(): int
    {
        if ($this->isIndex || $this->isAction) {
            return 0;
        }

        if ($this->raw) {
            return 1;
        }

        return 2;
    }
}
