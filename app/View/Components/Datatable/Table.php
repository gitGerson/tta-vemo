<?php

namespace App\View\Components\Datatable;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Table extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $id,
        public array $columns,
        public string $class = 'table table-striped',
        public string $responsiveClass = 'table-responsive text-nowrap',
    )
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.datatable.table');
    }
}
