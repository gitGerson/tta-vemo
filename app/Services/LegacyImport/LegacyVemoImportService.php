<?php

namespace App\Services\LegacyImport;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Imports a legacy VEMO MySQL dump (an older snapshot of this same app) into the
 * current schema. Unlike the ITAM importer this PRESERVES the original primary
 * keys and inserts into a freshly-migrated empty database, so every internal FK
 * reference (records.vehicle_id, maintenances_details.maintenance_id, user
 * self-refs, ...) stays valid with zero remapping.
 */
class LegacyVemoImportService
{
    /**
     * Legacy tables we import, paired with their current target table.
     *
     * @var array<string, string>
     */
    private const SUPPORTED = [
        'company' => 'companies',
        'users' => 'users',
        'drivers' => 'drivers',
        'vehicles' => 'vehicles',
        'records' => 'records',
        'maintenances' => 'maintenances',
        'maintenances_details' => 'maintenances_details',
        'note' => 'note',
    ];

    /**
     * Framework / queue / session tables that are silently ignored.
     *
     * @var list<string>
     */
    private const FRAMEWORK = [
        'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'sessions',
        'migrations', 'password_reset_tokens', 'personal_access_tokens',
    ];

    /**
     * @var array<string, int>
     */
    private array $imported = [];

    /**
     * @var array<string, int>
     */
    private array $skipped = [];

    /**
     * @var list<string>
     */
    private array $warnings = [];

    /**
     * @param  array{tables: array<string, array<int, string>>, rows: array<string, list<array{legacy_id: int|string|null, attributes: array<string, mixed>}>>, skipped_statements: array<string, int>}  $dump
     * @return array{mode: string, tables: array<string, int>, imported: array<string, int>, skipped: array<string, int>, warnings: list<string>, report_path: string|null}
     */
    public function import(array $dump, bool $commit = false, int $chunkSize = 500): array
    {
        $this->reset();

        $rows = $dump['rows'];

        if ($commit) {
            $this->assertEmptyTargets();

            Schema::disableForeignKeyConstraints();

            try {
                DB::transaction(function () use ($rows, $chunkSize): void {
                    $this->runImport($rows, true, $chunkSize);
                    $this->backfillCompanies();
                });
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        } else {
            $this->runImport($rows, false, $chunkSize);
        }

        foreach ($this->unsupportedTables($rows) as $table => $count) {
            $this->warnings[] = "Skipped legacy table [{$table}] ({$count} rows).";
        }

        return $this->writeReport([
            'mode' => $commit ? 'commit' : 'dry-run',
            'tables' => collect($rows)->map(fn (array $tableRows): int => count($tableRows))->all(),
            'imported' => $this->imported,
            'skipped' => $this->skipped,
            'warnings' => $this->warnings,
            'report_path' => null,
        ]);
    }

    /**
     * @param  array<string, list<array{legacy_id: int|string|null, attributes: array<string, mixed>}>>  $rows
     */
    private function runImport(array $rows, bool $commit, int $chunkSize): void
    {
        // Dependency-safe order: parents before children.
        $this->importTable($rows, 'company', 'companies', $commit, $chunkSize, fn (array $a): array => [
            'id' => $a['id'],
            'name' => $this->string($a['name']),
            'code' => $this->string($a['code']),
            'notes' => $this->string($a['desc']),
            'created_at' => $a['created_at'] ?? null,
            'updated_at' => $a['updated_at'] ?? null,
        ]);

        $this->importTable($rows, 'users', 'users', $commit, $chunkSize, fn (array $a): array => [
            'id' => $a['id'],
            'name' => $this->string($a['name']),
            'email' => $this->string($a['email']),
            'email_verified_at' => $a['email_verified_at'] ?? null,
            'password' => (string) ($a['password'] ?? ''),
            'remember_token' => $this->string($a['remember_token']),
            'guid' => $this->string($a['guid'] ?? null),
            'username' => $this->string($a['username'] ?? null),
            'domain' => $this->string($a['domain'] ?? null),
            'created_by' => $a['created_by'] ?? null,
            'updated_by' => $a['updated_by'] ?? null,
            'deleted_by' => $a['deleted_by'] ?? null,
            'created_at' => $a['created_at'] ?? null,
            'updated_at' => $a['updated_at'] ?? null,
            'deleted_at' => $a['deleted_at'] ?? null,
        ]);

        $this->importTable($rows, 'drivers', 'drivers', $commit, $chunkSize, fn (array $a): array => [
            'id' => $a['id'],
            'image' => $this->string($a['image']),
            'nik' => $this->string($a['nik']),
            'name' => $this->string($a['name']),
            'address' => (string) ($a['address'] ?? ''),
            'no_identity' => (string) ($a['no_identity'] ?? ''),
            'residence' => (string) ($a['residence'] ?? ''),
            'date_birth' => $a['date_birth'] ?? null,
            'no_phone_1' => (string) ($a['no_phone_1'] ?? ''),
            'no_phone_2' => $this->string($a['no_phone_2']),
            'entry_date' => $a['entry_date'] ?? null,
            'out_date' => $a['out_date'] ?? null,
            'status' => (string) ($a['status'] ?? 'ACTIVE'),
            'created_at' => $a['created_at'] ?? null,
            'updated_at' => $a['updated_at'] ?? null,
            'deleted_at' => $a['deleted_at'] ?? null,
        ]);

        // plat_old is dropped in the current schema; type/fuel_type strings are kept
        // and the new FK ids (company_id/fuel_type_id/vehicle_type_id/vehicle_brand_id)
        // are left null (company_id backfilled post-insert).
        $this->importTable($rows, 'vehicles', 'vehicles', $commit, $chunkSize, fn (array $a): array => [
            'id' => $a['id'],
            'type' => (string) ($a['type'] ?? ''),
            'entry_year' => (string) ($a['entry_year'] ?? ''),
            'mileage' => $this->string($a['mileage']),
            'plat_new' => (string) ($a['plat_new'] ?? ''),
            'status' => (string) ($a['status'] ?? 'ACTIVE'),
            'fuel_type' => (string) ($a['fuel_type'] ?? 'BBM'),
            'mtc' => (string) ($a['mtc'] ?? 'NO'),
            'desc' => $this->string($a['desc']),
            'created_at' => $a['created_at'] ?? null,
            'updated_at' => $a['updated_at'] ?? null,
            'deleted_at' => $a['deleted_at'] ?? null,
        ]);

        // type_maintenances is intentionally NOT imported from the dump (the legacy
        // rows have no `jenis`). It is a standalone master list with no FK to
        // maintenances, so it is reseeded from public/master_type_maintenance.csv
        // via TypeMaintenanceSeeder instead.

        $this->importTable($rows, 'records', 'records', $commit, $chunkSize, fn (array $a): array => [
            'id' => $a['id'],
            'driver_1' => (string) ($a['driver_1'] ?? ''),
            'driver_2' => $this->string($a['driver_2']),
            'vehicle_id' => $a['vehicle_id'],
            'no_pol' => (string) ($a['no_pol'] ?? ''),
            'destination' => (string) ($a['destination'] ?? ''),
            'company_id' => $a['company_id'],
            'start_km' => $this->stringOrNull($a['start_km'] ?? null),
            'finish_km' => (string) ($a['finish_km'] ?? ''),
            'totals' => (string) ($a['totals'] ?? ''),
            'bbm' => (string) ($a['bbm'] ?? ''),
            'fuel_unit_price' => null,
            'idr' => (string) ($a['idr'] ?? ''),
            'scale' => (string) ($a['scale'] ?? ''),
            'date' => $a['date'] ?? null,
            'created_at' => $a['created_at'] ?? null,
            'updated_at' => $a['updated_at'] ?? null,
            'deleted_at' => $a['deleted_at'] ?? null,
        ]);

        // get_request -> created_by; get_approval/approved_by_name are dropped in the
        // current schema; company_id backfilled post-insert from the vehicle plate.
        $this->importTable($rows, 'maintenances', 'maintenances', $commit, $chunkSize, fn (array $a): array => [
            'id' => $a['id'],
            'code_mt' => (string) ($a['code_mt'] ?? ''),
            'no_pol' => (string) ($a['no_pol'] ?? ''),
            'total_amount' => $this->decimal($a['total_amount'] ?? 0) ?? 0,
            'date_in' => $a['date_in'] ?? null,
            'date_check' => $a['date_check'] ?? null,
            'date_out' => $a['date_out'] ?? null,
            'mileage' => $this->string($a['mileage']),
            'description' => $this->string($a['description']),
            'isServiceRoutine' => (int) ($a['isServiceRoutine'] ?? 0),
            'status' => $this->string($a['status']) ?? 'DRAFT',
            'viewed_at' => $a['viewed_at'] ?? null,
            'viewed_by' => $a['viewed_by'] ?? null,
            'is_confirmed' => (int) ($a['is_confirmed'] ?? 0),
            'confirmed_at' => $a['confirmed_at'] ?? null,
            'confirmed_by' => $a['confirmed_by'] ?? null,
            'confirmation_notes' => $this->string($a['confirmation_notes']),
            'requested_by_name' => $this->string($a['requested_by_name'] ?? null),
            'created_by' => $a['get_request'] ?? null,
            'created_at' => $a['created_at'] ?? null,
            'updated_at' => $a['updated_at'] ?? null,
            'deleted_at' => $a['deleted_at'] ?? null,
        ]);

        $this->importTable($rows, 'maintenances_details', 'maintenances_details', $commit, $chunkSize, fn (array $a): array => [
            'id' => $a['id'],
            'maintenance_id' => $a['maintenance_id'],
            'maintenance_name' => (string) ($a['maintenance_name'] ?? ''),
            'price' => $this->decimal($a['price'] ?? 0) ?? 0,
            'qty' => $this->stringOrNull($a['qty'] ?? null),
            'subtotal' => $this->decimal($a['subtotal'] ?? 0) ?? 0,
            'material' => $this->string($a['material']),
            'created_at' => $a['created_at'] ?? null,
            'updated_at' => $a['updated_at'] ?? null,
            'deleted_at' => $a['deleted_at'] ?? null,
        ]);

        $this->importTable($rows, 'note', 'note', $commit, $chunkSize, fn (array $a): array => [
            'id' => $a['id'],
            'maintenances_id' => $a['maintenances_id'],
            'image' => (string) ($a['image'] ?? ''),
            'url' => $this->string($a['url']),
            'created_at' => $a['created_at'] ?? null,
            'updated_at' => $a['updated_at'] ?? null,
        ]);
    }

    /**
     * @param  array<string, list<array{legacy_id: int|string|null, attributes: array<string, mixed>}>>  $rows
     * @param  callable(array<string, mixed>): array<string, mixed>  $transformer
     */
    private function importTable(array $rows, string $legacyTable, string $targetTable, bool $commit, int $chunkSize, callable $transformer): void
    {
        foreach (array_chunk($rows[$legacyTable] ?? [], $chunkSize) as $chunk) {
            $batch = [];

            foreach ($chunk as $row) {
                $batch[] = $transformer($row['attributes']);
                $this->increment($this->imported, $legacyTable);
            }

            if ($commit && $batch !== []) {
                DB::table($targetTable)->insert($batch);
            }
        }
    }

    /**
     * Best-effort tenancy normalization (commit only). Legacy vehicles carry no
     * company; records do. Maintenances link to vehicles by plate string.
     */
    private function backfillCompanies(): void
    {
        DB::table('vehicles')->whereNull('company_id')->update([
            'company_id' => DB::raw('(select r.company_id from records r where r.vehicle_id = vehicles.id group by r.company_id order by count(*) desc limit 1)'),
        ]);

        DB::table('maintenances')->whereNull('company_id')->update([
            'company_id' => DB::raw('(select v.company_id from vehicles v where v.plat_new = maintenances.no_pol limit 1)'),
        ]);

        $vehiclesWithoutCompany = DB::table('vehicles')->whereNull('company_id')->count();

        if ($vehiclesWithoutCompany > 0) {
            $this->warnings[] = "{$vehiclesWithoutCompany} vehicles have no derivable company_id (no related records).";
        }
    }

    private function assertEmptyTargets(): void
    {
        $populated = [];

        foreach (array_unique(array_values(self::SUPPORTED)) as $table) {
            if (DB::table($table)->exists()) {
                $populated[] = $table;
            }
        }

        if ($populated !== []) {
            throw new RuntimeException(
                'Target tables already contain data: '.implode(', ', $populated).
                '. The legacy importer preserves primary keys and only runs against a fresh database.'
            );
        }
    }

    /**
     * @param  array<string, list<array{legacy_id: int|string|null, attributes: array<string, mixed>}>>  $rows
     * @return array<string, int>
     */
    private function unsupportedTables(array $rows): array
    {
        $unsupported = [];

        foreach ($rows as $table => $tableRows) {
            if (array_key_exists($table, self::SUPPORTED) || in_array($table, self::FRAMEWORK, true)) {
                continue;
            }

            $unsupported[$table] = count($tableRows);
        }

        return $unsupported;
    }

    /**
     * @param  array{mode: string, tables: array<string, int>, imported: array<string, int>, skipped: array<string, int>, warnings: list<string>, report_path: string|null}  $report
     * @return array{mode: string, tables: array<string, int>, imported: array<string, int>, skipped: array<string, int>, warnings: list<string>, report_path: string|null}
     */
    private function writeReport(array $report): array
    {
        $path = 'legacy-import/reports/legacy-vemo-import-'.now()->format('Ymd-His').'-'.Str::random(6).'.json';

        try {
            Storage::disk('local')->put($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '{}');
            $report['report_path'] = Storage::disk('local')->path($path);
        } catch (Throwable $exception) {
            $report['warnings'][] = 'Import report could not be written: '.$exception->getMessage();
        }

        return $report;
    }

    private function string(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    /**
     * Like string() but keeps "0" (e.g. legacy start_km/qty stored as text).
     */
    private function stringOrNull(mixed $value): ?string
    {
        return $value === null ? null : (string) $value;
    }

    private function decimal(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * @param  array<string, int>  $bucket
     */
    private function increment(array &$bucket, string $key): void
    {
        $bucket[$key] = ($bucket[$key] ?? 0) + 1;
    }

    private function reset(): void
    {
        $this->imported = [];
        $this->skipped = [];
        $this->warnings = [];
    }
}
