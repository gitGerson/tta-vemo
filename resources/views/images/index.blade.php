@extends('layouts.sneat')

@section('title')
    Image Gallery
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Image Gallery</h5>
                <div>
                    @if(auth()->user()->hasPermission('images.delete'))
                        <a href="{{ route('images.trash') }}" class="btn btn-secondary me-2">
                            <i class="bx bx-trash me-1"></i> Trash
                        </a>
                    @endif
                    @if(auth()->user()->hasPermission('images.create'))
                        <a href="{{ route('images.create') }}" class="btn btn-primary">
                            <i class="bx bx-plus me-1"></i> Upload Image
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table id="images-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Title</th>
                                <th>File Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Uploaded By</th>
                                <th>Uploaded At</th>
                                <th>Actions</th>
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
            $('#images-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('images.data') }}',
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'file_name',
                        name: 'file_name'
                    },
                    {
                        data: 'file_type',
                        name: 'file_type'
                    },
                    {
                        data: 'formatted_size',
                        name: 'formatted_size',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'created_by_name',
                        name: 'created_by_name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'formatted_date',
                        name: 'formatted_date',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                dom: 'lBfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
                ],
            });
        });
    </script>
@endpush
