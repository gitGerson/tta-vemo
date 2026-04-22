<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('permissions')
            ->whereIn('name', array_keys($this->removedPermissions()))
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('permissions')->insertOrIgnore(
            array_values($this->removedPermissions())
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function removedPermissions(): array
    {
        $now = now();

        return [
            'images.access' => ['name' => 'images.access', 'display_name' => 'Images', 'description' => 'Can access Images section', 'module' => 'images', 'parent' => null, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            'images.view' => ['name' => 'images.view', 'display_name' => 'Images - View', 'description' => 'Can view images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            'images.create' => ['name' => 'images.create', 'display_name' => 'Images - Upload', 'description' => 'Can upload images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            'images.edit' => ['name' => 'images.edit', 'display_name' => 'Images - Edit', 'description' => 'Can edit images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            'images.delete' => ['name' => 'images.delete', 'display_name' => 'Images - Delete', 'description' => 'Can delete images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            'images.restore' => ['name' => 'images.restore', 'display_name' => 'Images - Restore', 'description' => 'Can restore deleted images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            'images.force_delete' => ['name' => 'images.force_delete', 'display_name' => 'Images - Force Delete', 'description' => 'Can permanently delete images', 'module' => 'images', 'parent' => 'images.access', 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            'master.categories.view' => ['name' => 'master.categories.view', 'display_name' => 'Categories - View', 'description' => 'Can view categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            'master.categories.create' => ['name' => 'master.categories.create', 'display_name' => 'Categories - Create', 'description' => 'Can create categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            'master.categories.edit' => ['name' => 'master.categories.edit', 'display_name' => 'Categories - Edit', 'description' => 'Can edit categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            'master.categories.delete' => ['name' => 'master.categories.delete', 'display_name' => 'Categories - Delete', 'description' => 'Can delete categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            'master.categories.restore' => ['name' => 'master.categories.restore', 'display_name' => 'Categories - Restore', 'description' => 'Can restore deleted categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            'master.categories.force_delete' => ['name' => 'master.categories.force_delete', 'display_name' => 'Categories - Force Delete', 'description' => 'Can permanently delete categories', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            'master.products.view' => ['name' => 'master.products.view', 'display_name' => 'Products - View', 'description' => 'Can view products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 7, 'created_at' => $now, 'updated_at' => $now],
            'master.products.create' => ['name' => 'master.products.create', 'display_name' => 'Products - Create', 'description' => 'Can create products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 8, 'created_at' => $now, 'updated_at' => $now],
            'master.products.edit' => ['name' => 'master.products.edit', 'display_name' => 'Products - Edit', 'description' => 'Can edit products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 9, 'created_at' => $now, 'updated_at' => $now],
            'master.products.delete' => ['name' => 'master.products.delete', 'display_name' => 'Products - Delete', 'description' => 'Can delete products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            'master.products.restore' => ['name' => 'master.products.restore', 'display_name' => 'Products - Restore', 'description' => 'Can restore deleted products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 11, 'created_at' => $now, 'updated_at' => $now],
            'master.products.force_delete' => ['name' => 'master.products.force_delete', 'display_name' => 'Products - Force Delete', 'description' => 'Can permanently delete products', 'module' => 'master', 'parent' => 'master.access', 'sort_order' => 12, 'created_at' => $now, 'updated_at' => $now],
            'maintenances.restore' => ['name' => 'maintenances.restore', 'display_name' => 'Maintenances - Restore', 'description' => 'Can restore maintenances', 'module' => 'maintenances', 'parent' => 'maintenances.access', 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
            'maintenances.force_delete' => ['name' => 'maintenances.force_delete', 'display_name' => 'Maintenances - Force Delete', 'description' => 'Can permanently delete maintenances', 'module' => 'maintenances', 'parent' => 'maintenances.access', 'sort_order' => 7, 'created_at' => $now, 'updated_at' => $now],
        ];
    }
};
