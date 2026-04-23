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
            ['image_id' => $image->id],
            $image
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
        $image->delete();

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

        return redirect()->route('images.trash')
            ->with('success', 'Image restored successfully');
    }

    /**
     * Permanently delete image.
     */
    public function forceDelete($id)
    {
        $image = Image::withTrashed()->findOrFail($id);
        // Delete from MinIO
        Storage::disk('s3')->delete($image->file_path);

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
            ->addColumn('row_url', function ($image) {
                return route('images.show', $image->id);
            })
            ->addColumn('action', function ($image) {
                return view('datatable.actions.images', compact('image'))->render();
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
                return view('datatable.actions.images-trash', compact('image'))->render();
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
