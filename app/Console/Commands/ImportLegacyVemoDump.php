<?php

namespace App\Console\Commands;

use App\Services\LegacyImport\LegacySqlDumpReader;
use App\Services\LegacyImport\LegacyVemoImportService;
use Illuminate\Console\Command;
use RuntimeException;

class ImportLegacyVemoDump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'legacy:vemo-import
        {path : Absolute SQL dump filepath}
        {--commit : Persist changes instead of previewing}
        {--chunk=500 : Number of rows inserted per batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Preview or import a legacy VEMO SQL dump into the current schema, preserving primary keys';

    public function handle(LegacySqlDumpReader $reader, LegacyVemoImportService $importer): int
    {
        $path = (string) $this->argument('path');
        $chunkSize = max(1, (int) $this->option('chunk'));
        $commit = (bool) $this->option('commit');

        if (! $this->isAbsolutePath($path)) {
            $this->error('The SQL dump path must be absolute.');

            return self::FAILURE;
        }

        try {
            $dump = $reader->read($path);
            $result = $importer->import(
                dump: $dump,
                commit: $commit,
                chunkSize: $chunkSize,
            );
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info($commit ? 'Legacy VEMO import committed.' : 'Legacy VEMO import dry-run completed.');
        $this->line('Parsed tables: '.count($dump['tables']));
        $this->line('Parsed rows: '.array_sum(array_map('count', $dump['rows'])));
        $this->line('Report: '.($result['report_path'] ?? 'not written'));

        $this->printCounts('Imported', $result['imported']);
        $this->printCounts('Skipped', $result['skipped']);

        foreach ($result['warnings'] as $warning) {
            $this->warn($warning);
        }

        if ($commit) {
            $this->newLine();
            $this->comment('Next, seed data that is intentionally not imported from the dump:');
            $this->comment('  php artisan db:seed --class=RolePermissionSeeder   # roles & permissions from config/permission.php');
            $this->comment('  php artisan db:seed --class=CompanySeeder          # remaining companies from the ITAM employee API (skips dump\'s TTI/CTR)');
            $this->comment('  php artisan db:seed --class=TypeMaintenanceSeeder  # maintenance types from public/master_type_maintenance.csv');
            $this->comment('  php artisan destinations:import --commit           # destinations master from database/data/destinations.json');
        }

        return self::SUCCESS;
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1
            || str_starts_with($path, '/')
            || str_starts_with($path, '\\\\');
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function printCounts(string $label, array $counts): void
    {
        if ($counts === []) {
            $this->line("{$label}: none");

            return;
        }

        ksort($counts);

        foreach ($counts as $table => $count) {
            $this->line("{$label} {$table}: {$count}");
        }
    }
}
