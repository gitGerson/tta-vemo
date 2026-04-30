<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class HashIdRoutingTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('s3');
    }

    public function test_route_helpers_generate_hashids_for_core_modules(): void
    {
        $user = $this->createUser();
        $role = $this->createRole();
        $category = $this->createCategory();
        $product = $this->createProduct($category);
        $image = $this->createImage();

        $this->assertSame(route('users.show', $user), url('/users/' . $user->hashid));
        $this->assertSame(route('roles.show', $role), url('/roles/' . $role->hashid));
        $this->assertSame(route('categories.show', $category), url('/categories/' . $category->hashid));
        $this->assertSame(route('products.show', $product), url('/products/' . $product->hashid));
        $this->assertSame(route('images.show', $image), url('/images/' . $image->hashid));

        $this->assertStringNotContainsString('/users/' . $user->id, route('users.show', $user));
        $this->assertStringNotContainsString('/roles/' . $role->id, route('roles.show', $role));
        $this->assertStringNotContainsString('/categories/' . $category->id, route('categories.show', $category));
        $this->assertStringNotContainsString('/products/' . $product->id, route('products.show', $product));
        $this->assertStringNotContainsString('/images/' . $image->id, route('images.show', $image));

        $this->assertSame(route('users.user-logs', $user), url('/users/' . $user->hashid . '/logs'));
        $this->assertSame(route('users.roles', $user), url('/users/' . $user->hashid . '/roles'));
        $this->assertSame(route('roles.permissions', $role), url('/roles/' . $role->hashid . '/permissions'));
        $this->assertSame(route('users.restore', $user), url('/users/' . $user->hashid . '/restore'));
        $this->assertSame(route('users.force-delete', $user), url('/users/' . $user->hashid . '/force-delete'));
        $this->assertSame(route('categories.restore', $category), url('/categories/' . $category->hashid . '/restore'));
        $this->assertSame(route('products.force-delete', $product), url('/products/' . $product->hashid . '/force-delete'));
        $this->assertSame(route('images.restore', $image), url('/images/' . $image->hashid . '/restore'));
    }

    public function test_hashid_routes_resolve_the_correct_models(): void
    {
        $user = $this->createUser();
        $role = $this->createRole();
        $category = $this->createCategory();
        $product = $this->createProduct($category);
        $image = $this->createImage();

        $this->assertSame($user->id, (new User())->resolveRouteBinding($user->hashid)?->id);
        $this->assertSame($role->id, (new Role())->resolveRouteBinding($role->hashid)?->id);
        $this->assertSame($category->id, (new Category())->resolveRouteBinding($category->hashid)?->id);
        $this->assertSame($product->id, (new Product())->resolveRouteBinding($product->hashid)?->id);
        $this->assertSame($image->id, (new Image())->resolveRouteBinding($image->hashid)?->id);
    }

    public function test_soft_deleted_hash_routes_resolve_trashed_models_with_with_trashed_binding(): void
    {
        $user = $this->createUser();
        $user->delete();
        $trashedUser = User::withTrashed()->findOrFail($user->id);

        $category = $this->createCategory();
        $category->delete();
        $trashedCategory = Category::withTrashed()->findOrFail($category->id);

        $product = $this->createProduct($category);
        $product->delete();
        $trashedProduct = Product::withTrashed()->findOrFail($product->id);

        $image = $this->createImage();
        $image->delete();
        $trashedImage = Image::withTrashed()->findOrFail($image->id);

        $this->assertSame($user->id, (new User())->resolveSoftDeletableRouteBinding($trashedUser->hashid)?->id);
        $this->assertSame($category->id, (new Category())->resolveSoftDeletableRouteBinding($trashedCategory->hashid)?->id);
        $this->assertSame($product->id, (new Product())->resolveSoftDeletableRouteBinding($trashedProduct->hashid)?->id);
        $this->assertSame($image->id, (new Image())->resolveSoftDeletableRouteBinding($trashedImage->hashid)?->id);
    }

    public function test_legacy_numeric_urls_do_not_resolve(): void
    {
        $user = $this->createUser();
        $role = $this->createRole();
        $category = $this->createCategory();
        $product = $this->createProduct($category);
        $image = $this->createImage();

        $this->assertNull((new User())->resolveRouteBinding((string) $user->id));
        $this->assertNull((new Role())->resolveRouteBinding((string) $role->id));
        $this->assertNull((new Category())->resolveRouteBinding((string) $category->id));
        $this->assertNull((new Product())->resolveRouteBinding((string) $product->id));
        $this->assertNull((new Image())->resolveRouteBinding((string) $image->id));
    }

    protected function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'User ' . Str::random(6),
            'email' => Str::lower(Str::random(8)) . '@example.com',
            'username' => Str::lower(Str::random(8)),
        ], $attributes));
    }

    protected function createRole(array $attributes = []): Role
    {
        $suffix = Str::lower(Str::random(8));

        return Role::create(array_merge([
            'name' => 'role_' . $suffix,
            'display_name' => 'Role ' . $suffix,
            'description' => 'Generated test role',
            'is_active' => true,
        ], $attributes));
    }

    protected function createCategory(array $attributes = []): Category
    {
        $suffix = Str::lower(Str::random(8));

        return Category::create(array_merge([
            'name' => 'Category ' . $suffix,
            'slug' => 'category-' . $suffix,
            'description' => 'Generated test category',
            'is_active' => true,
            'sort_order' => 1,
        ], $attributes));
    }

    protected function createProduct(Category $category, array $attributes = []): Product
    {
        $suffix = Str::lower(Str::random(8));

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => 'Product ' . $suffix,
            'slug' => 'product-' . $suffix,
            'sku' => 'SKU-' . strtoupper($suffix),
            'description' => 'Generated test product',
            'price' => 10000,
            'stock' => 10,
            'is_active' => true,
        ], $attributes));
    }

    protected function createImage(array $attributes = []): Image
    {
        $suffix = Str::lower(Str::random(8));

        Storage::disk('s3')->put('images/' . $suffix . '.jpg', 'image');

        return Image::create(array_merge([
            'title' => 'Image ' . $suffix,
            'description' => 'Generated test image',
            'file_name' => $suffix . '.jpg',
            'file_path' => 'images/' . $suffix . '.jpg',
            'file_type' => 'image/jpeg',
            'file_size' => 5,
        ], $attributes));
    }
}
