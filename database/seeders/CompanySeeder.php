<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'Central Transport',
                'code' => 'CTR001',
                'desc' => 'Perusahaan logistik utama.',
            ],
            [
                'name' => 'Harbor Lines',
                'code' => 'HBL002',
                'desc' => 'Distribusi antar pulau.',
            ],
            [
                'name' => 'Metro Freight',
                'code' => 'MTF003',
                'desc' => 'Pengiriman regional harian.',
            ],
        ];

        foreach ($companies as $company) {
            Company::updateOrCreate(['code' => $company['code']], $company);
        }
    }
}
