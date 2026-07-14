<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\VehiclePhoto;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehiclePhotoCommentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_authorized_user_can_post_a_comment(): void
    {
        $user = $this->userWithPhotoViewPermission();
        $this->actingAs($user);

        $photo = VehiclePhoto::factory()->create();

        $this->post(route('vehicle-photos.comments.store', $photo), ['body' => 'Foto sudah lengkap.'])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'commentable_type' => $photo->getMorphClass(),
            'commentable_id' => $photo->id,
            'user_id' => $user->id,
            'body' => 'Foto sudah lengkap.',
        ]);
    }

    public function test_comment_body_is_sanitized_on_store(): void
    {
        $user = $this->userWithPhotoViewPermission();
        $this->actingAs($user);

        $photo = VehiclePhoto::factory()->create();

        $this->post(route('vehicle-photos.comments.store', $photo), [
            'body' => '<p>Aman</p><script>alert(1)</script>',
        ])->assertRedirect();

        $body = $photo->comments()->sole()->body;
        $this->assertStringNotContainsString('<script', $body);
        $this->assertStringContainsString('Aman', $body);
    }

    public function test_visually_empty_comment_is_rejected(): void
    {
        $this->actingAs($this->userWithPhotoViewPermission());
        $photo = VehiclePhoto::factory()->create();

        $this->post(route('vehicle-photos.comments.store', $photo), ['body' => '<p></p>'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_author_can_delete_own_comment(): void
    {
        $user = $this->userWithPhotoViewPermission();
        $this->actingAs($user);

        $photo = VehiclePhoto::factory()->create();
        $comment = $photo->comments()->create(['body' => 'Hapus saya', 'user_id' => $user->id]);

        $this->delete(route('vehicle-photos.comments.destroy', [$photo, $comment]))
            ->assertRedirect();

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_user_cannot_delete_another_users_comment(): void
    {
        $author = $this->userWithPhotoViewPermission();
        $photo = VehiclePhoto::factory()->create();
        $comment = $photo->comments()->create(['body' => 'Milik orang lain', 'user_id' => $author->id]);

        $this->actingAs($this->userWithPhotoViewPermission());

        $this->delete(route('vehicle-photos.comments.destroy', [$photo, $comment]))
            ->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'deleted_at' => null]);
    }

    private function userWithPhotoViewPermission(): User
    {
        $user = User::factory()->create();
        $role = Role::create([
            'name' => 'photo-viewer-'.$user->id,
            'display_name' => 'Photo Viewer',
        ]);

        collect([
            'operational.vehicle_photos.view',
            'management.tenant.bypass',
        ])->each(function (string $name) use ($role): void {
            $permission = Permission::firstOrCreate(
                ['name' => $name],
                ['display_name' => $name, 'module' => 'operational'],
            );

            $role->permissions()->attach($permission->id);
        });

        $user->roles()->attach($role->id, ['assigned_at' => now()]);
        $user->load('roles.permissions');

        return $user;
    }
}
