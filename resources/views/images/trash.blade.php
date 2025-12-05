@extends('layouts.sneat')

@section('title')
    Trash - Images
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
                <h5 class="mb-0">Deleted Images</h5>
                <div>
                    <a href="{{ route('images.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Back to Images
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table id="images-trash-table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Title</th>
                                <th>File Name</th>
                                <th>Size</th>
                                <th>Uploaded By</th>
                                <th>Updated By</th>
                                <th>Deleted By</th>
                                <th>Deleted At</th>
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
            $('#images-trash-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('images.trash-data') }}',
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
                        data: 'updated_by_name',
                        name: 'updated_by_name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'deleted_by_name',
                        name: 'deleted_by_name',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'deleted_at',
                        name: 'deleted_at'
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
