<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormDemoRequest;
use App\Services\FilePondUploadService;

class FormDemoController extends Controller
{
    public function __construct(protected FilePondUploadService $filePondUploads)
    {
    }

    public function index(): \Illuminate\View\View
    {
        $roleTemplateOptions = [
            ['id' => 'admin_template', 'display_name' => 'Administrator Template'],
            ['id' => 'user_manager_template', 'display_name' => 'User Manager Template'],
            ['id' => 'viewer_template', 'display_name' => 'Viewer Template'],
        ];

        $statusOptions = [
            'draft' => 'Draft',
            'review' => 'In Review',
            'published' => 'Published',
        ];

        return view('form-demo.index', compact('roleTemplateOptions', 'statusOptions'));
    }

    public function store(FormDemoRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();

        $nativeFilePath = $this->filePondUploads->storeFromRequestField(
            $request,
            'attachment_native',
            'tmp/form-demo/native'
        );

        $filePondFilePath = $this->filePondUploads->storeFromRequestField(
            $request,
            'attachment_filepond',
            'tmp/form-demo/filepond'
        );

        $demoResult = [
            'title' => $validated['title'] ?? null,
            'quantity' => isset($validated['quantity']) ? (float) $validated['quantity'] : null,
            'status' => $validated['status'] ?? null,
            'role_template' => $validated['role_template'] ?? null,
            'featured' => $request->boolean('featured'),
            'is_active' => $request->boolean('is_active'),
            'publish_date' => $validated['publish_date'] ?? null,
            'event_date' => $validated['event_date'] ?? null,
            'booking_window' => $validated['booking_window'] ?? null,
            'summary' => $validated['summary'] ?? null,
            'body' => $validated['body'] ?? null,
            'attachment_native' => $nativeFilePath,
            'attachment_filepond' => $filePondFilePath,
            'attachment_filepond_existing' => $validated['attachment_filepond_existing'] ?? null,
        ];

        return redirect()
            ->route('form-demo.index')
            ->with('success', 'Demo form submitted successfully.')
            ->with('demo_result', $demoResult)
            ->withInput($request->except(['attachment_native', 'attachment_filepond']));
    }
}
