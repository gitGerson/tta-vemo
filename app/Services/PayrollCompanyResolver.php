<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PayrollCompanyResolver
{
    /**
     * @return array{code: string|null, name: string, id: int|null, exists: bool}|null
     */
    public function normalize(mixed $employee): ?array
    {
        if (is_object($employee)) {
            $employee = (array) $employee;
        }

        if (! is_array($employee)) {
            return null;
        }

        $code = $this->firstFilled($employee, [
            'CompanyCode',
            'company_code',
            'CompanyID',
            'CompanyId',
            'company_id',
            'CompCode',
            'comp_code',
            'BusinessUnitCode',
            'business_unit_code',
        ]);
        $name = $this->firstFilled($employee, [
            'CompanyName',
            'company_name',
            'Company',
            'company',
            'CompName',
            'comp_name',
            'BusinessUnit',
            'business_unit',
            'OrganizationName',
            'organization_name',
            'LegalEntityName',
            'legal_entity_name',
        ]);

        if ($name === null && $code === null) {
            return null;
        }

        $name ??= $code;
        $company = $this->findExisting($code, $name);

        return [
            'code' => $code,
            'name' => $name,
            'id' => $company?->id,
            'exists' => $company !== null,
        ];
    }

    /**
     * @param  array<int, int|string>  $companyIds
     * @return array<int, int>
     */
    public function mergeResolvedCompanyIds(array $companyIds, ?string $sourceCompanyCode, ?string $sourceCompanyName): array
    {
        $ids = collect($companyIds)
            ->filter(fn (mixed $id): bool => filled($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0);

        $company = $this->resolveOrCreate($sourceCompanyCode, $sourceCompanyName);

        if ($company !== null) {
            $ids->push($company->id);
        }

        return $ids->unique()->values()->all();
    }

    public function resolveOrCreate(?string $code, ?string $name): ?Company
    {
        $code = $this->normalizeValue($code);
        $name = $this->normalizeValue($name);

        if ($code === null && $name === null) {
            return null;
        }

        $name ??= $code;

        $company = $this->findExisting($code, $name, withTrashed: true);

        if ($company !== null) {
            if (method_exists($company, 'trashed') && $company->trashed()) {
                $company->restore();
            }

            return $company;
        }

        return Company::query()->create([
            'code' => $code,
            'name' => $name,
            'notes' => 'Created from JPayroll employee data.',
        ]);
    }

    private function findExisting(?string $code, string $name, bool $withTrashed = false): ?Company
    {
        $query = $withTrashed ? Company::withTrashed() : Company::query();

        return $query
            ->where(function ($query) use ($code, $name): void {
                if ($code !== null) {
                    $query->where('code', $code);
                }

                $query->orWhere('name', $name);
            })
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $keys
     */
    private function firstFilled(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = Arr::get($data, $key);
            $value = $this->normalizeValue($value);

            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    private function normalizeValue(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = Str::squish((string) $value);

        return $value === '' ? null : $value;
    }
}
