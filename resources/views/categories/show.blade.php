@extends('layouts.sneat')

@section('title')
    Detail Kategori
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Kategori: {{ $category->name }}</h5>
                <div>
                    @if(auth()->user()->hasPermission('master.categories.edit'))
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning me-2">
                            <i class="bx bx-edit me-1"></i> Edit
                        </a>
                    @endif
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($category->image)
                            <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="img-fluid rounded">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center rounded" style="height: 200px;">
                                <span class="text-muted">Tidak ada gambar</span>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tr>
                                <th style="width: 150px;">Nama</th>
                                <td>{{ $category->name }}</td>
                            </tr>
                            <tr>
                                <th>Slug</th>
                                <td><code>{{ $category->slug }}</code></td>
                            </tr>
                            <tr>
                                <th>Deskripsi</th>
                                <td>{{ $category->description ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Urutan</th>
                                <td>{{ $category->sort_order }}</td>
                            </tr>
                            <tr>
                                <th>Jumlah Produk</th>
                                <td>{{ $category->products->count() }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <hr>

                <h6 class="mb-3">Informasi Audit</h6>
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th style="width: 150px;">Dibuat Pada</th>
                                <td>{{ $category->created_at->format('d M Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Dibuat Oleh</th>
                                <td>{{ $category->creator ? $category->creator->name : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th style="width: 150px;">Diperbarui Pada</th>
                                <td>{{ $category->updated_at->format('d M Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Diperbarui Oleh</th>
                                <td>{{ $category->updater ? $category->updater->name : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($category->products->count() > 0)
                    <hr>
                    <h6 class="mb-3">Produk dalam Kategori ini</h6>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama Produk</th>
                                    <th>SKU</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($category->products as $index => $product)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
                                        </td>
                                        <td>{{ $product->sku ?: '-' }}</td>
                                        <td>{{ $product->formatted_price }}</td>
                                        <td>{{ $product->stock }}</td>
                                        <td>
                                            @if($product->is_active)
                                                <span class="badge bg-success">Aktif</span>
                                            @else
                                                <span class="badge bg-secondary">Nonaktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
