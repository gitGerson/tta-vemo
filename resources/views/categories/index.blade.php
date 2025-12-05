@extends('layouts.sneat')

@section('title')
    Daftar Kategori
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
                <h5 class="mb-0">Daftar Kategori</h5>
                <div>
                    @if(auth()->user()->hasPermission('master.categories.delete'))
                        <a href="{{ route('categories.trash') }}" class="btn btn-secondary me-2">
                            <i class="bx bx-trash me-1"></i> Tempat Sampah
                        </a>
                    @endif
                    @if(auth()->user()->hasPermission('master.categories.create'))
                        <a href="{{ route('categories.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Tambah Kategori
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table id="categories-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Gambar</th>
                                <th>Nama</th>
                                <th>Slug</th>
                                <th>Jumlah Produk</th>
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
            $('#categories-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('categories.data') }}',
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'image_preview', name: 'image', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'slug', name: 'slug' },
                    { data: 'products_count', name: 'products_count', orderable: false, searchable: false },
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
