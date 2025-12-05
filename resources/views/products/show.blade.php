@extends('layouts.sneat')

@section('title')
    Detail Produk
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detail Produk: {{ $product->name }}</h5>
                <div>
                    @if(auth()->user()->hasPermission('master.products.edit'))
                        <a href="{{ route('products.edit', $product) }}" class="btn btn-warning me-2">
                            <i class="bx bx-edit me-1"></i> Edit
                        </a>
                    @endif
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($product->image)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="img-fluid rounded">
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
                                <td>{{ $product->name }}</td>
                            </tr>
                            <tr>
                                <th>Kategori</th>
                                <td>
                                    @if($product->category)
                                        <a href="{{ route('categories.show', $product->category) }}">{{ $product->category->name }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Slug</th>
                                <td><code>{{ $product->slug }}</code></td>
                            </tr>
                            <tr>
                                <th>SKU</th>
                                <td>{{ $product->sku ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Deskripsi</th>
                                <td>{{ $product->description ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Harga</th>
                                <td><strong>{{ $product->formatted_price }}</strong></td>
                            </tr>
                            <tr>
                                <th>Stok</th>
                                <td>
                                    @if($product->stock > 10)
                                        <span class="badge bg-success">{{ $product->stock }}</span>
                                    @elseif($product->stock > 0)
                                        <span class="badge bg-warning">{{ $product->stock }}</span>
                                    @else
                                        <span class="badge bg-danger">Habis</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($product->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
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
                                <td>{{ $product->created_at->format('d M Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Dibuat Oleh</th>
                                <td>{{ $product->creator ? $product->creator->name : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <th style="width: 150px;">Diperbarui Pada</th>
                                <td>{{ $product->updated_at->format('d M Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Diperbarui Oleh</th>
                                <td>{{ $product->updater ? $product->updater->name : '-' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
