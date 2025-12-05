@extends('layouts.sneat')

@section('title')
    Daftar Produk
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Produk</h5>
                <div>
                    @if(auth()->user()->hasPermission('master.products.delete'))
                        <a href="{{ route('products.trash') }}" class="btn btn-secondary me-2">
                            <i class="bx bx-trash me-1"></i> Tempat Sampah
                        </a>
                    @endif
                    @if(auth()->user()->hasPermission('master.products.create'))
                        <a href="{{ route('products.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Tambah Produk
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table id="products-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Gambar</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th>SKU</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#products-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('products.data') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'image_preview', name: 'image', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'category_name', name: 'category.name' },
                    { data: 'sku', name: 'sku' },
                    { data: 'formatted_price', name: 'price' },
                    { data: 'stock', name: 'stock' },
                    { data: 'status', name: 'is_active', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                dom: 'lBfrtip',
                buttons: [
                    { extend: 'copy', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'csv', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'excel', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'pdf', exportOptions: { columns: ':not(:last-child)' } },
                    { extend: 'print', exportOptions: { columns: ':not(:last-child)' } },
                    'colvis'
                ],
            });
        });
    </script>
@endpush
