@props([
    'table',
    'title' => null,
    'tableClass' => 'table table-striped',
    'responsiveClass' => '',
])

{{-- Backward-compatible alias: <x-datatable> renders the DataTables engine of <x-table>. --}}
<x-table
    :table="$table"
    engine="datatables"
    :title="$title"
    :table-class="$tableClass"
    :responsive-class="$responsiveClass"
>
    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset
</x-table>
