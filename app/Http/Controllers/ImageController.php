<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\UserLog;
use App\Services\FilePondUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function __construct(protected FilePondUploadService $filePondUploads) {}

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
            'image' => ['required', $this->filePondUploads->imageUploadValidationRule(10240)],
        ]);

        try {
            $filePath = $this->filePondUploads->storeFromRequestField($request, 'image', 'images', 's3');

            if (! $filePath) {
                throw new \RuntimeException('File upload tidak ditemukan.');
            }

            $image = Image::create([
                'title' => $request->title,
                'description' => $request->description,
                'file_name' => basename($filePath),
                'file_path' => $filePath,
                'file_type' => Storage::disk('s3')->mimeType($filePath) ?: 'application/octet-stream',
                'file_size' => Storage::disk('s3')->size($filePath),
            ]);

            return redirect()->route('images.index')
                ->with('success', 'Image uploaded successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to upload image: '.$e->getMessage());
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
            'image' => [
                'nullable',
                $this->filePondUploads->imageUploadValidationRule(
                    10240,
                    ['jpeg', 'png', 'jpg', 'gif', 'webp'],
                    [$image->url, $image->file_path]
                ),
            ],
        ]);

        try {
            $image->title = $request->title;
            $image->description = $request->description;

            if ($request->hasFile('image') || $this->filePondUploads->isTempPath($request->input('image'))) {
                $filePath = $this->filePondUploads->storeFromRequestField($request, 'image', 'images', 's3');

                if (! $filePath) {
                    throw new \RuntimeException('File upload tidak ditemukan.');
                }

                Storage::disk('s3')->delete($image->file_path);

                $image->file_name = basename($filePath);
                $image->file_path = $filePath;
                $image->file_type = Storage::disk('s3')->mimeType($filePath) ?: 'application/octet-stream';
                $image->file_size = Storage::disk('s3')->size($filePath);
            }

            $image->save();

            return redirect()->route('images.index')
                ->with('success', 'Image updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update image: '.$e->getMessage());
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
    public function restore(Image $image)
    {
        $image->deleted_by = null;
        $image->restore();

        return redirect()->route('images.trash')
            ->with('success', 'Image restored successfully');
    }

    /**
     * Permanently delete image.
     */
    public function forceDelete(Image $image)
    {
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
                return route('images.show', $image);
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
