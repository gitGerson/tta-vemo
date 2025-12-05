<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating categories...');

        $categories = [
            [
                'name' => 'Elektronik',
                'slug' => 'elektronik',
                'description' => 'Perangkat elektronik seperti smartphone, laptop, tablet, dan aksesori elektronik lainnya.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Fashion Pria',
                'slug' => 'fashion-pria',
                'description' => 'Pakaian, sepatu, dan aksesori fashion untuk pria.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Fashion Wanita',
                'slug' => 'fashion-wanita',
                'description' => 'Pakaian, sepatu, tas, dan aksesori fashion untuk wanita.',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Makanan & Minuman',
                'slug' => 'makanan-minuman',
                'description' => 'Berbagai jenis makanan dan minuman.',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Kesehatan & Kecantikan',
                'slug' => 'kesehatan-kecantikan',
                'description' => 'Produk kesehatan, perawatan kulit, dan kosmetik.',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Rumah & Dekorasi',
                'slug' => 'rumah-dekorasi',
                'description' => 'Perabotan rumah, dekorasi, dan perlengkapan rumah tangga.',
                'is_active' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Olahraga & Outdoor',
                'slug' => 'olahraga-outdoor',
                'description' => 'Peralatan olahraga dan aktivitas outdoor.',
                'is_active' => true,
                'sort_order' => 7,
            ],
            [
                'name' => 'Buku & Alat Tulis',
                'slug' => 'buku-alat-tulis',
                'description' => 'Buku, majalah, dan perlengkapan alat tulis.',
                'is_active' => true,
                'sort_order' => 8,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info(count($categories) . ' categories created successfully!');
    }
}
