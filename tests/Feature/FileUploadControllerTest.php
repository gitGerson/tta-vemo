<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authenticated_user_can_process_and_revert_a_temporary_upload(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('uploads.process'), [
            'filepond' => UploadedFile::fake()->create('demo.png', 10, 'image/png'),
        ]);

        $response->assertOk();

        $tempPath = $response->getContent();

        $this->assertIsString($tempPath);
        $this->assertStringStartsWith('tmp/filepond/', $tempPath);
        Storage::disk('local')->assertExists($tempPath);

        $this->actingAs($user)
            ->call(
                'DELETE',
                route('uploads.revert'),
                server: ['CONTENT_TYPE' => 'text/plain'],
                content: $tempPath
            )
            ->assertOk();

        Storage::disk('local')->assertMissing($tempPath);
    }

    public function test_authenticated_user_can_load_a_temporary_upload(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $path = 'tmp/filepond/demo.pdf';

        Storage::disk('local')->put($path, 'demo');

        $this->actingAs($user)
            ->get(route('uploads.load', ['source' => $path]))
            ->assertOk()
            ->assertHeader('Content-Disposition', 'inline; filename="demo.pdf"');
    }
}
