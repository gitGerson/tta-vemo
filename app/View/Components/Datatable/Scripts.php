<?php

namespace App\View\Components\Datatable;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Scripts extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $tableId,
        public string $ajax,
        public array|string|null $order = null,
        public bool $exportExcludeLastColumn = true,
        public ?string $variable = null,
        public bool $clickableRows = false,
        public string $rowUrlField = 'row_url',
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.datatable.scripts');
    }
}
