<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

/**
 * Controller for handling FilePond file uploads
 * Files are temporarily stored and then moved to S3 on form submission
 */
class FileUploadController extends Controller
{
    /**
     * Process and temporarily store an uploaded file from FilePond
     *
     * @return string The temporary file path
     */
    public function process(Request $request): string
    {
        // Grab all files from the request
        $files = $request->allFiles();

        if (empty($files)) {
            abort(422, 'No files were uploaded.');
        }

        if (count($files) > 1) {
            abort(422, 'Only 1 file can be uploaded at a time.');
        }

        // Get the request key (file input name)
        $requestKey = array_key_first($files);

        // Handle both single and multiple file uploads
        /** @var UploadedFile $file */
        $file = is_array($request->file($requestKey))
            ? $request->file($requestKey)[0]
            : $request->file($requestKey);

        // Validate file type
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'svg'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $allowedExtensions)) {
            abort(422, 'Invalid file type. Allowed: '.implode(', ', $allowedExtensions));
        }

        // Validate file size (max 3MB)
        if ($file->getSize() > 3 * 1024 * 1024) {
            abort(422, 'File size exceeds 3MB limit.');
        }

        // Generate unique directory and filename
        $tempDir = 'tmp/'.now()->timestamp.'-'.Str::random(20);
        $filename = Str::random(40).'.'.$extension;
        $tempPath = $tempDir.'/'.$filename;

        // For images, compress before storing
        if (in_array($extension, ['jpeg', 'jpg', 'png', 'gif'])) {
            $compressedImage = Image::read($file->getPathname())
                ->scaleDown(800, 800)
                ->encodeByExtension($extension, quality: 80);

            Storage::disk('local')->put($tempPath, (string) $compressedImage);
        } else {
            // Store non-image files directly
            Storage::disk('local')->put($tempPath, file_get_contents($file->getPathname()));
        }

        // Return the temporary path for FilePond to use
        return $tempPath;
    }

    /**
     * Revert/delete a temporarily uploaded file
     * Called when user removes a file before form submission
     *
     * @return \Illuminate\Http\Response
     */
    public function revert(Request $request)
    {
        // Get the file path from the request body
        $path = $request->getContent();

        if (empty($path)) {
            return response('No file path provided', 422);
        }

        // Safety check - only allow deletion from tmp directory
        if (! str_starts_with($path, 'tmp/')) {
            return response('Invalid file path', 422);
        }

        // Delete the file and its directory
        if (Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);

            // Try to delete the parent directory if empty
            $dir = dirname($path);
            if (Storage::disk('local')->exists($dir)) {
                $files = Storage::disk('local')->files($dir);
                if (empty($files)) {
                    Storage::disk('local')->deleteDirectory($dir);
                }
            }
        }

        return response('', 200);
    }

    /**
     * Load an existing file for FilePond to display
     * Used when editing existing records
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function load(Request $request)
    {
        $source = $request->query('source');

        if (empty($source)) {
            return response('No source provided', 422);
        }

        // For S3 files, redirect to the URL
        if (Storage::disk('s3')->exists($source)) {
            /** @var \Illuminate\Filesystem\AwsS3V3Adapter $disk */
            $disk = Storage::disk('s3');

            return redirect($disk->url($source));
        }

        // For local files
        if (Storage::disk('local')->exists($source)) {
            $content = Storage::disk('local')->get($source);
            /** @var \Illuminate\Filesystem\FilesystemAdapter $localDisk */
            $localDisk = Storage::disk('local');
            $mimeType = $localDisk->mimeType($source);

            return response($content, 200, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="'.basename($source).'"',
            ]);
        }

        return response('File not found', 404);
    }
}
