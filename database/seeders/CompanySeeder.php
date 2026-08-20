<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class CompanySeeder extends Seeder
{
    private const API_URL = 'https://dev-itam.tongtji.com/api/employee';

    private const API_KEY = 'D16MKGizcA4Ig94/CRsVlWY5Iz11rSeesUc6NjuiJIc=';

    /**
     * Seed companies from the distinct `company` names in the ITAM employee API.
     */
    public function run(): void
    {
        $response = Http::withHeaders(['x-api-key' => self::API_KEY])
            ->timeout(30)
            ->retry(2, 300)
            ->get(self::API_URL);

        if (! $response->successful()) {
            $this->command?->warn("Employee API request failed (HTTP {$response->status()}).");

            return;
        }

        $names = collect($response->json('data', []))
            ->pluck('company')
            ->map(fn ($name): string => trim((string) $name))
            ->filter()
            ->unique()
            ->values();

        // Existing codes guard the generated code's uniqueness (code is unique).
        $takenCodes = Company::pluck('code')->all();
        $created = 0;

        foreach ($names as $name) {
            if (Company::where('name', $name)->exists()) {
                continue;
            }

            $code = $this->uniqueCode($this->baseCode($name), $takenCodes);
            $takenCodes[] = $code;

            Company::create(['name' => $name, 'code' => $code]);
            $created++;
        }

        $this->command?->info("{$created} companies created ({$names->count()} distinct names from API).");
    }

    private function baseCode(string $name): string
    {
        $initials = '';

        foreach (preg_split('/\s+/', trim($name)) ?: [] as $word) {
            $word = preg_replace('/[^A-Za-z0-9]/', '', $word);

            if ($word !== '') {
                $initials .= strtoupper($word[0]);
            }
        }

        if (strlen($initials) < 2) {
            $initials = substr(strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $name)), 0, 4);
        }

        return substr($initials, 0, 20);
    }

    /**
     * @param  list<string>  $taken
     */
    private function uniqueCode(string $base, array $taken): string
    {
        $lower = array_map('strtolower', $taken);
        $code = $base;
        $i = 1;

        while (in_array(strtolower($code), $lower, true)) {
            $suffix = (string) (++$i);
            $code = substr($base, 0, 20 - strlen($suffix)).$suffix;
        }

        return $code;
    }
}
