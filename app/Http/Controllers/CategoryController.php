<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $data = $request->only(['name', 'slug', 'description', 'is_active', 'sort_order']);

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($request->name);
            }

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $filePath = 'categories/' . $fileName;

                // Upload to S3
                Storage::disk('s3')->put($filePath, file_get_contents($file));
                $data['image'] = $filePath;
            }

            Category::create($data);

            return redirect()->route('categories.index')
                ->with('success', 'Kategori berhasil ditambahkan');
        } catch (\Exception $e) {
            Log::error('Category Create Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menambahkan kategori: ' . $e->getMessage());
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
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $data = $request->only(['name', 'slug', 'description', 'is_active', 'sort_order']);

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($request->name);
            }

            if ($request->hasFile('image')) {
                // Delete old image from S3
                if ($category->image) {
                    Storage::disk('s3')->delete($category->image);
                }

                $file = $request->file('image');
                $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $filePath = 'categories/' . $fileName;

                // Upload new image to S3
                Storage::disk('s3')->put($filePath, file_get_contents($file));
                $data['image'] = $filePath;
            }

            $category->update($data);

            return redirect()->route('categories.index')
                ->with('success', 'Kategori berhasil diperbarui');
        } catch (\Exception $e) {
            Log::error('Category Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui kategori: ' . $e->getMessage());
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
    public function restore($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->deleted_by = null;
        $category->restore();

        return redirect()->route('categories.trash')
            ->with('success', 'Kategori berhasil dipulihkan');
    }

    /**
     * Permanently delete category.
     */
    public function forceDelete($id)
    {
        $category = Category::withTrashed()->findOrFail($id);

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
                    return '<img src="' . Storage::disk('s3')->url($category->image) . '" alt="' . $category->name . '" style="max-width: 50px; max-height: 50px;">';
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
            ->addColumn('action', function ($category) {
                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';

                if (auth()->user()->hasPermission('master.categories.view')) {
                    $actions .= '<a class="dropdown-item" href="' . route('categories.show', $category->id) . '">
                        <i class="bx bx-show me-1"></i> Lihat
                    </a>';
                }

                if (auth()->user()->hasPermission('master.categories.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('categories.edit', $category->id) . '">
                        <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>';
                }

                if (auth()->user()->hasPermission('master.categories.delete')) {
                    $actions .= '<form action="' . route('categories.destroy', $category->id) . '" method="POST" style="display: inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus kategori ini?\')">
                            <i class="bx bx-trash me-1"></i> Hapus
                        </button>
                    </form>';
                }

                $actions .= '</div></div>';
                return $actions;
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
                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';

                if (auth()->user()->hasPermission('master.categories.restore')) {
                    $actions .= '<form action="' . route('categories.restore', $category->id) . '" method="POST" style="display: inline;">
                        ' . csrf_field() . '
                        <button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin memulihkan kategori ini?\')">
                            <i class="bx bx-refresh me-1"></i> Pulihkan
                        </button>
                    </form>';
                }

                if (auth()->user()->hasPermission('master.categories.force_delete')) {
                    $actions .= '<form action="' . route('categories.force-delete', $category->id) . '" method="POST" style="display: inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="dropdown-item" onclick="return confirm(\'Yakin ingin menghapus permanen? Data tidak dapat dipulihkan!\')">
                            <i class="bx bx-trash me-1"></i> Hapus Permanen
                        </button>
                    </form>';
                }

                $actions .= '</div></div>';
                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
