<?php

namespace App\Http\Controllers;

use App\Services\FilePondUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FileUploadController extends Controller
{
    public function __construct(protected FilePondUploadService $filePondUploads)
    {
    }

    public function process(Request $request): Response
    {
        $uploadedFile = collect($request->allFiles())->flatten()->first();

        if (! $uploadedFile) {
            abort(422, 'No file uploaded.');
        }

        validator(
            ['upload' => $uploadedFile],
            ['upload' => ['required', 'file', 'mimes:jpeg,png,jpg,gif,webp,pdf', 'max:51200']]
        )->validate();

        return response($this->filePondUploads->storeTemporaryUpload($uploadedFile), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function revert(Request $request): Response
    {
        $this->filePondUploads->deleteTemporaryUpload(trim($request->getContent()));

        return response('', 200);
    }

    public function load(Request $request): Response
    {
        $temporaryUpload = $this->filePondUploads->getTemporaryUpload((string) $request->query('source', ''));

        abort_unless($temporaryUpload !== null, 404);

        return response($temporaryUpload['contents'], 200)
            ->header('Content-Type', $temporaryUpload['mime_type'])
            ->header('Content-Disposition', 'inline; filename="'.$temporaryUpload['filename'].'"');
    }
}
