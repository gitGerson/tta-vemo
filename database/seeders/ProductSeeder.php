<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating products...');

        $categories = Category::all();

        if ($categories->isEmpty()) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');
            return;
        }

        $products = [
            // Elektronik
            [
                'category' => 'elektronik',
                'name' => 'Smartphone Samsung Galaxy A54',
                'sku' => 'ELEC-001',
                'description' => 'Smartphone Samsung Galaxy A54 dengan layar Super AMOLED 6.4 inch, kamera 50MP.',
                'price' => 5499000,
                'stock' => 50,
                'is_active' => true,
            ],
            [
                'category' => 'elektronik',
                'name' => 'Laptop ASUS VivoBook 14',
                'sku' => 'ELEC-002',
                'description' => 'Laptop ASUS VivoBook 14 dengan prosesor Intel Core i5, RAM 8GB, SSD 512GB.',
                'price' => 8999000,
                'stock' => 25,
                'is_active' => true,
            ],
            [
                'category' => 'elektronik',
                'name' => 'TWS Earbuds Sony WF-1000XM4',
                'sku' => 'ELEC-003',
                'description' => 'True Wireless Earbuds Sony dengan Active Noise Cancelling.',
                'price' => 3299000,
                'stock' => 100,
                'is_active' => true,
            ],
            // Fashion Pria
            [
                'category' => 'fashion-pria',
                'name' => 'Kemeja Formal Pria Lengan Panjang',
                'sku' => 'FASH-M-001',
                'description' => 'Kemeja formal pria berbahan katun premium, nyaman dipakai seharian.',
                'price' => 299000,
                'stock' => 200,
                'is_active' => true,
            ],
            [
                'category' => 'fashion-pria',
                'name' => 'Sepatu Sneakers Casual',
                'sku' => 'FASH-M-002',
                'description' => 'Sepatu sneakers casual dengan desain modern dan nyaman.',
                'price' => 549000,
                'stock' => 75,
                'is_active' => true,
            ],
            // Fashion Wanita
            [
                'category' => 'fashion-wanita',
                'name' => 'Dress Casual Wanita',
                'sku' => 'FASH-W-001',
                'description' => 'Dress casual wanita dengan motif bunga, cocok untuk berbagai acara.',
                'price' => 359000,
                'stock' => 150,
                'is_active' => true,
            ],
            [
                'category' => 'fashion-wanita',
                'name' => 'Tas Selempang Kulit Sintetis',
                'sku' => 'FASH-W-002',
                'description' => 'Tas selempang wanita berbahan kulit sintetis berkualitas tinggi.',
                'price' => 249000,
                'stock' => 80,
                'is_active' => true,
            ],
            // Makanan & Minuman
            [
                'category' => 'makanan-minuman',
                'name' => 'Kopi Arabika Premium 250gr',
                'sku' => 'FNB-001',
                'description' => 'Kopi arabika premium dari dataran tinggi Toraja.',
                'price' => 89000,
                'stock' => 300,
                'is_active' => true,
            ],
            [
                'category' => 'makanan-minuman',
                'name' => 'Teh Hijau Organik 100 Sachet',
                'sku' => 'FNB-002',
                'description' => 'Teh hijau organik dalam kemasan sachet praktis.',
                'price' => 125000,
                'stock' => 200,
                'is_active' => true,
            ],
            // Kesehatan & Kecantikan
            [
                'category' => 'kesehatan-kecantikan',
                'name' => 'Skincare Set Lengkap',
                'sku' => 'BEAUTY-001',
                'description' => 'Set skincare lengkap untuk perawatan wajah sehari-hari.',
                'price' => 459000,
                'stock' => 60,
                'is_active' => true,
            ],
            [
                'category' => 'kesehatan-kecantikan',
                'name' => 'Vitamin C 1000mg 30 Tablet',
                'sku' => 'HEALTH-001',
                'description' => 'Suplemen vitamin C untuk menjaga daya tahan tubuh.',
                'price' => 75000,
                'stock' => 500,
                'is_active' => true,
            ],
            // Rumah & Dekorasi
            [
                'category' => 'rumah-dekorasi',
                'name' => 'Lampu Meja LED Modern',
                'sku' => 'HOME-001',
                'description' => 'Lampu meja LED dengan desain modern dan hemat energi.',
                'price' => 189000,
                'stock' => 100,
                'is_active' => true,
            ],
            [
                'category' => 'rumah-dekorasi',
                'name' => 'Pot Tanaman Keramik Set 3',
                'sku' => 'HOME-002',
                'description' => 'Set 3 pot tanaman keramik dengan berbagai ukuran.',
                'price' => 149000,
                'stock' => 120,
                'is_active' => true,
            ],
            // Olahraga & Outdoor
            [
                'category' => 'olahraga-outdoor',
                'name' => 'Matras Yoga Premium 6mm',
                'sku' => 'SPORT-001',
                'description' => 'Matras yoga anti slip dengan ketebalan 6mm.',
                'price' => 199000,
                'stock' => 80,
                'is_active' => true,
            ],
            [
                'category' => 'olahraga-outdoor',
                'name' => 'Dumbbell Set 5kg x 2',
                'sku' => 'SPORT-002',
                'description' => 'Set dumbbell 5kg untuk latihan kekuatan.',
                'price' => 275000,
                'stock' => 50,
                'is_active' => true,
            ],
            // Buku & Alat Tulis
            [
                'category' => 'buku-alat-tulis',
                'name' => 'Buku Catatan Premium A5',
                'sku' => 'BOOK-001',
                'description' => 'Buku catatan A5 dengan cover kulit sintetis.',
                'price' => 65000,
                'stock' => 200,
                'is_active' => true,
            ],
            [
                'category' => 'buku-alat-tulis',
                'name' => 'Set Pena Gel 12 Warna',
                'sku' => 'BOOK-002',
                'description' => 'Set pena gel dengan 12 warna berbeda.',
                'price' => 49000,
                'stock' => 300,
                'is_active' => true,
            ],
        ];

        foreach ($products as $productData) {
            $category = $categories->where('slug', $productData['category'])->first();

            if ($category) {
                Product::create([
                    'category_id' => $category->id,
                    'name' => $productData['name'],
                    'slug' => Str::slug($productData['name']),
                    'sku' => $productData['sku'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'stock' => $productData['stock'],
                    'is_active' => $productData['is_active'],
                ]);
            }
        }

        $this->command->info(count($products) . ' products created successfully!');
    }
}
