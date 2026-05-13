<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\FilePondUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function __construct(protected FilePondUploadService $filePondUploads) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('categories.index');
    }

    /**
     * Display a listing of soft deleted categories.
     */
    public function trash()
    {
        return view('categories.trash');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image' => ['nullable', $this->filePondUploads->imageUploadValidationRule()],
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $data = $request->only(['name', 'slug', 'description', 'is_active', 'sort_order']);

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($request->name);
            }

            if ($request->hasFile('image') || $this->filePondUploads->isTempPath($request->input('image'))) {
                $filePath = $this->filePondUploads->storeFromRequestField($request, 'image', 'categories', 's3');
                $data['image'] = $filePath;
            }

            Category::create($data);

            return redirect()->route('categories.index')
                ->with('success', 'Kategori berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Category Create Error: '.$e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan kategori: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->load(['creator', 'updater', 'deleter', 'products']);

        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories,slug,'.$category->id,
            'description' => 'nullable|string',
            'image' => [
                'nullable',
                $this->filePondUploads->imageUploadValidationRule(
                    2048,
                    ['jpeg', 'png', 'jpg', 'gif', 'webp'],
                    [$category->image, $category->image_url]
                ),
            ],
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $data = $request->only(['name', 'slug', 'description', 'is_active', 'sort_order']);

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($request->name);
            }

            if ($request->hasFile('image') || $this->filePondUploads->isTempPath($request->input('image'))) {
                $filePath = $this->filePondUploads->storeFromRequestField($request, 'image', 'categories', 's3');

                if ($category->image) {
                    Storage::disk('s3')->delete($category->image);
                }

                $data['image'] = $filePath;
            }

            $category->update($data);

            return redirect()->route('categories.index')
                ->with('success', 'Kategori berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Category Update Error: '.$e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui kategori: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus');
    }

    /**
     * Restore soft deleted category.
     */
    public function restore(Category $category)
    {
        $category->deleted_by = null;
        $category->restore();

        return redirect()->route('categories.trash')
            ->with('success', 'Kategori berhasil dipulihkan');
    }

    /**
     * Permanently delete category.
     */
    public function forceDelete(Category $category)
    {
        // Delete image from S3
        if ($category->image) {
            Storage::disk('s3')->delete($category->image);
        }

        $category->forceDelete();

        return redirect()->route('categories.trash')
            ->with('success', 'Kategori berhasil dihapus permanen');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $categories = Category::with('products')->select(['id', 'name', 'slug', 'image', 'is_active', 'sort_order', 'created_at']);

        return datatables()->of($categories)
            ->addIndexColumn()
            ->addColumn('image_preview', function ($category) {
                if ($category->image) {
                    return '<img src="'.Storage::disk('s3')->url($category->image).'" alt="'.$category->name.'" style="max-width: 50px; max-height: 50px;">';
                }

                return '<span class="text-muted">No Image</span>';
            })
            ->addColumn('status', function ($category) {
                return $category->is_active
                    ? '<span class="badge bg-success">Aktif</span>'
                    : '<span class="badge bg-secondary">Nonaktif</span>';
            })
            ->addColumn('products_count', function ($category) {
                return $category->products->count();
            })
            ->addColumn('row_url', function ($category) {
                return route('categories.show', $category);
            })
            ->addColumn('action', function ($category) {
                return view('datatable.actions.categories', compact('category'))->render();
            })
            ->rawColumns(['image_preview', 'status', 'action'])
            ->make(true);
    }

    /**
     * Get trash data for DataTables.
     */
    public function getTrashData(Request $request)
    {
        $categories = Category::onlyTrashed()
            ->with(['creator', 'deleter'])
            ->select(['id', 'name', 'slug', 'deleted_at', 'deleted_by']);

        return datatables()->of($categories)
            ->addColumn('deleted_by_name', function ($category) {
                return $category->deleter ? $category->deleter->name : '-';
            })
            ->addColumn('action', function ($category) {
                return view('datatable.actions.categories-trash', compact('category'))->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
