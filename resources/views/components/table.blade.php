@props([
    'table',
    'engine' => 'datatables',
    'title' => null,
    'tableClass' => 'table table-striped',
    'responsiveClass' => 'table-responsive text-nowrap',
    'paginationSize' => 10,
    'paginationSizeSelector' => [10, 25, 50, 100],
    'layout' => 'fitColumns',
    'responsiveLayout' => false,
    'selectableRows' => false,
    'showSearch' => true,
    'showColvis' => true,
    'showExport' => true,
])

{{--
    Engine-agnostic table component. The same config-driven DataTable backend
    (columns / query / actions / clickableRoute) drives any frontend library;
    pick the renderer with engine="datatables" (default) or engine="tabulator".
--}}
<x-dynamic-component
    :component="'table.' . $engine"
    :table="$table"
    :title="$title"
    :table-class="$tableClass"
    :responsive-class="$responsiveClass"
    :pagination-size="$paginationSize"
    :pagination-size-selector="$paginationSizeSelector"
    :layout="$layout"
    :responsive-layout="$responsiveLayout"
    :selectable-rows="$selectableRows"
    :show-search="$showSearch"
    :show-colvis="$showColvis"
    :show-export="$showExport"
>
    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset

    @isset($tools)
        <x-slot:tools>{{ $tools }}</x-slot:tools>
    @endisset
</x-dynamic-component>
