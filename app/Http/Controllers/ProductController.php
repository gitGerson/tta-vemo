<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * Display a listing of soft deleted products.
     */
    public function trash()
    {
        return view('products.trash');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductStoreRequest $request)
    {
        try {
            $data = $request->validated();

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($request->string('name'));
            }

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $filePath = 'products/' . $fileName;

                // Upload to S3
                Storage::disk('s3')->put($filePath, file_get_contents($file));
                $data['image'] = $filePath;
            }

            Product::create($data);

            return redirect()->route('products.index')
                ->with('success', 'Produk berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Product Create Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load(['category', 'creator', 'updater', 'deleter']);
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUpdateRequest $request, Product $product)
    {
        try {
            $data = $request->validated();

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($request->string('name'));
            }

            if ($request->hasFile('image')) {
                // Delete old image from S3
                if ($product->image) {
                    Storage::disk('s3')->delete($product->image);
                }

                $file = $request->file('image');
                $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $filePath = 'products/' . $fileName;

                // Upload new image to S3
                Storage::disk('s3')->put($filePath, file_get_contents($file));
                $data['image'] = $filePath;
            }

            $product->update($data);

            return redirect()->route('products.index')
                ->with('success', 'Produk berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Product Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus');
    }

    /**
     * Restore soft deleted product.
     */
    public function restore($id)
    {
        $product = Product::withTrashed()->findOrFail($id);
        $product->deleted_by = null;
        $product->restore();

        return redirect()->route('products.trash')
            ->with('success', 'Produk berhasil dipulihkan');
    }

    /**
     * Permanently delete product.
     */
    public function forceDelete($id)
    {
        $product = Product::withTrashed()->findOrFail($id);

        // Delete image from S3
        if ($product->image) {
            Storage::disk('s3')->delete($product->image);
        }

        $product->forceDelete();

        return redirect()->route('products.trash')
            ->with('success', 'Produk berhasil dihapus permanen');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $products = Product::with('category')->select(['id', 'category_id', 'name', 'slug', 'sku', 'price', 'stock', 'image', 'is_active', 'created_at']);

        return datatables()->of($products)
            ->addIndexColumn()
            ->addColumn('image_preview', function ($product) {
                if ($product->image) {
                    return '<img src="' . Storage::disk('s3')->url($product->image) . '" alt="' . $product->name . '" style="max-width: 50px; max-height: 50px;">';
                }
                return '<span class="text-muted">No Image</span>';
            })
            ->addColumn('category_name', function ($product) {
                return $product->category ? $product->category->name : '-';
            })
            ->addColumn('formatted_price', function ($product) {
                return 'Rp ' . number_format($product->price, 0, ',', '.');
            })
            ->addColumn('status', function ($product) {
                return $product->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Nonaktif</span>';
            })
            ->addColumn('row_url', function ($product) {
                return route('products.show', $product->id);
            })
            ->addColumn('action', function ($product) {
                return view('datatable.actions.products', compact('product'))->render();
            })
            ->rawColumns(['image_preview', 'status', 'action'])
            ->make(true);
    }

    /**
     * Get trash data for DataTables.
     */
    public function getTrashData(Request $request)
    {
        $products = Product::onlyTrashed()
            ->with(['category', 'deleter'])
            ->select(['id', 'category_id', 'name', 'slug', 'deleted_at', 'deleted_by']);

        return datatables()->of($products)
            ->addColumn('category_name', function ($product) {
                return $product->category ? $product->category->name : '-';
            })
            ->addColumn('deleted_by_name', function ($product) {
                return $product->deleter ? $product->deleter->name : '-';
            })
            ->addColumn('action', function ($product) {
                return view('datatable.actions.products-trash', compact('product'))->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
