<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('images.index');
    }

    /**
     * Display a listing of soft deleted images.
     */
    public function trash()
    {
        return view('images.trash');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('images.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
        ]);

        try {
            $file = $request->file('image');
            $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $filePath = 'images/' . $fileName;

            // Upload to MinIO (S3)
            Storage::disk('s3')->put($filePath, file_get_contents($file));

            $image = Image::create([
                'title' => $request->title,
                'description' => $request->description,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);

            UserLog::log(
                'CREATE_IMAGE',
                "Created image: {$image->title}",
                null,
                null,
                [
                    'image_id' => $image->id,
                    'file_name' => $fileName,
                    'file_size' => $file->getSize(),
                ]
            );

            return redirect()->route('images.index')
                ->with('success', 'Image uploaded successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to upload image: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Image $image)
    {
        $image->load(['creator', 'updater', 'deleter']);

        UserLog::log(
            'VIEW_IMAGE',
            "Viewed image: {$image->title}",
            null,
            null,
            ['image_id' => $image->id]
        );

        return view('images.show', compact('image'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Image $image)
    {
        return view('images.edit', compact('image'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Image $image)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB max
        ]);

        try {
            $oldData = [
                'title' => $image->title,
                'description' => $image->description,
                'file_name' => $image->file_name,
            ];

            $image->title = $request->title;
            $image->description = $request->description;

            // If new image uploaded, replace the old one
            if ($request->hasFile('image')) {
                // Delete old image from MinIO
                Storage::disk('s3')->delete($image->file_path);

                $file = $request->file('image');
                $fileName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
                $filePath = 'images/' . $fileName;

                // Upload new image to MinIO
                Storage::disk('s3')->put($filePath, file_get_contents($file));

                $image->file_name = $fileName;
                $image->file_path = $filePath;
                $image->file_type = $file->getClientMimeType();
                $image->file_size = $file->getSize();
            }

            $image->save();

            UserLog::log(
                'UPDATE_IMAGE',
                "Updated image: {$image->title}",
                null,
                null,
                [
                    'image_id' => $image->id,
                    'old_data' => $oldData,
                    'new_data' => [
                        'title' => $image->title,
                        'description' => $image->description,
                        'file_name' => $image->file_name,
                    ],
                ]
            );

            return redirect()->route('images.index')
                ->with('success', 'Image updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update image: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Image $image)
    {
        $imageTitle = $image->title;
        $imageFileName = $image->file_name;

        $image->delete();

        UserLog::log(
            'DELETE_IMAGE',
            "Soft deleted image: {$imageTitle}",
            null,
            null,
            [
                'image_id' => $image->id,
                'file_name' => $imageFileName,
            ]
        );

        return redirect()->route('images.index')
            ->with('success', 'Image deleted successfully');
    }

    /**
     * Restore soft deleted image.
     */
    public function restore($id)
    {
        $image = Image::withTrashed()->findOrFail($id);
        $image->deleted_by = null;
        $image->restore();

        UserLog::log(
            'RESTORE_IMAGE',
            "Restored image: {$image->title}",
            null,
            null,
            ['image_id' => $image->id]
        );

        return redirect()->route('images.trash')
            ->with('success', 'Image restored successfully');
    }

    /**
     * Permanently delete image.
     */
    public function forceDelete($id)
    {
        $image = Image::withTrashed()->findOrFail($id);
        $imageTitle = $image->title;
        $imageFileName = $image->file_name;

        // Delete from MinIO
        Storage::disk('s3')->delete($image->file_path);

        UserLog::log(
            'FORCE_DELETE_IMAGE',
            "Permanently deleted image: {$imageTitle}",
            null,
            null,
            [
                'image_id' => $image->id,
                'file_name' => $imageFileName,
            ]
        );

        $image->forceDelete();

        return redirect()->route('images.trash')
            ->with('success', 'Image permanently deleted');
    }

    /**
     * Get data for DataTables.
     */
    public function getData(Request $request)
    {
        $images = Image::with('creator')->select(['id', 'title', 'file_name', 'file_type', 'file_size', 'created_at', 'created_by']);

        return datatables()->of($images)
            ->addIndexColumn()
            ->addColumn('formatted_size', function ($image) {
                return $image->formatted_size;
            })
            ->addColumn('created_by_name', function ($image) {
                return $image->creator ? $image->creator->name : '-';
            })
            ->addColumn('formatted_date', function ($image) {
                return $image->created_at->format('d M Y H:i');
            })
            ->addColumn('action', function ($image) {
                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';

                // View action
                if (auth()->user()->hasPermission('images.view')) {
                    $actions .= '<a class="dropdown-item" href="' . route('images.show', $image->id) . '">
                        <i class="bx bx-show me-1"></i> View
                    </a>';
                }

                // Edit action
                if (auth()->user()->hasPermission('images.edit')) {
                    $actions .= '<a class="dropdown-item" href="' . route('images.edit', $image->id) . '">
                        <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>';
                }

                // Download action
                $actions .= '<a class="dropdown-item" href="' . $image->url . '" download target="_blank">
                    <i class="bx bx-download me-1"></i> Download
                </a>';

                // Delete action
                if (auth()->user()->hasPermission('images.delete')) {
                    $actions .= '<form action="' . route('images.destroy', $image->id) . '" method="POST" style="display: inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="dropdown-item" onclick="return confirm(\'Are you sure you want to delete this image?\')">
                            <i class="bx bx-trash me-1"></i> Delete
                        </button>
                    </form>';
                }

                $actions .= '</div></div>';

                return $actions;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get trash data for DataTables.
     */
    public function getTrashData(Request $request)
    {
        $images = Image::onlyTrashed()
            ->with(['creator', 'updater', 'deleter'])
            ->select(['id', 'title', 'file_name', 'file_size', 'deleted_at', 'created_by', 'updated_by', 'deleted_by']);

        return datatables()->of($images)
            ->addColumn('formatted_size', function ($image) {
                return $image->formatted_size;
            })
            ->addColumn('created_by_name', function ($image) {
                return $image->creator ? $image->creator->name : '-';
            })
            ->addColumn('updated_by_name', function ($image) {
                return $image->updater ? $image->updater->name : '-';
            })
            ->addColumn('deleted_by_name', function ($image) {
                return $image->deleter ? $image->deleter->name : '-';
            })
            ->addColumn('action', function ($image) {
                $actions = '<div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    <div class="dropdown-menu">';

                // Restore action
                if (auth()->user()->hasPermission('images.restore')) {
                    $actions .= '<form action="' . route('images.restore', $image->id) . '" method="POST" style="display: inline;">
                        ' . csrf_field() . '
                        <button type="submit" class="dropdown-item" onclick="return confirm(\'Are you sure you want to restore this image?\')">
                            <i class="bx bx-refresh me-1"></i> Restore
                        </button>
                    </form>';
                }

                // Force delete action
                if (auth()->user()->hasPermission('images.force_delete')) {
                    $actions .= '<form action="' . route('images.force-delete', $image->id) . '" method="POST" style="display: inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="dropdown-item" onclick="return confirm(\'Are you sure you want to permanently delete this image? This cannot be undone!\')">
                            <i class="bx bx-trash me-1"></i> Delete Permanently
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
