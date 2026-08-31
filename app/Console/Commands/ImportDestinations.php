<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use JsonException;

/**
 * Restores the destinations master from the legacy VEMO dump export
 * (database/data/destinations.json). Primary keys, timestamps and soft deletes
 * are preserved so records that reference a destination by name keep matching.
 */
class ImportDestinations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'destinations:import
        {--file=database/data/destinations.json : JSON export, relative to the project root or absolute}
        {--commit : Persist changes instead of previewing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import the destinations master from a JSON export, preserving ids, timestamps and soft deletes';

    public function handle(): int
    {
        $path = (string) $this->option('file');

        if (! $this->isAbsolutePath($path)) {
            $path = base_path($path);
        }

        if (! is_file($path)) {
            $this->error("File not found: {$path}");

            return self::FAILURE;
        }

        try {
            $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error('Invalid JSON: '.$exception->getMessage());

            return self::FAILURE;
        }

        $rows = is_array($payload['rows'] ?? null) ? $payload['rows'] : null;

        if ($rows === null) {
            $this->error('The export must contain a "rows" array.');

            return self::FAILURE;
        }

        $prepared = [];

        foreach ($rows as $index => $row) {
            $id = $row['id'] ?? null;
            $name = $row['name'] ?? null;

            if (! is_numeric($id) || ! is_string($name) || trim($name) === '') {
                $this->error("Row #{$index} needs a numeric id and a non-empty name.");

                return self::FAILURE;
            }

            $prepared[] = [
                'id' => (int) $id,
                'name' => trim($name),
                'desc' => $row['desc'] ?? null,
                'created_at' => $row['created_at'] ?? null,
                'updated_at' => $row['updated_at'] ?? null,
                'deleted_at' => $row['deleted_at'] ?? null,
            ];
        }

        $existingIds = DB::table('destinations')->pluck('id')->all();
        $existing = array_flip(array_map('intval', $existingIds));

        $inserts = array_values(array_filter($prepared, fn (array $row): bool => ! isset($existing[$row['id']])));
        $updates = array_values(array_filter($prepared, fn (array $row): bool => isset($existing[$row['id']])));

        $commit = (bool) $this->option('commit');

        if ($commit) {
            DB::transaction(function () use ($inserts, $updates): void {
                foreach (array_chunk($inserts, 200) as $chunk) {
                    DB::table('destinations')->insert($chunk);
                }

                foreach ($updates as $row) {
                    DB::table('destinations')->where('id', $row['id'])->update([
                        'name' => $row['name'],
                        'desc' => $row['desc'],
                        'created_at' => $row['created_at'],
                        'updated_at' => $row['updated_at'],
                        'deleted_at' => $row['deleted_at'],
                    ]);
                }
            });
        }

        $this->info($commit ? 'Destinations import committed.' : 'Destinations import dry-run completed. Re-run with --commit to persist.');
        $this->line('Source: '.($payload['source'] ?? basename($path)));
        $this->line('Rows in export: '.count($prepared));
        $this->line('Inserted: '.count($inserts));
        $this->line('Updated: '.count($updates));
        $this->line('Soft deleted in export: '.count(array_filter($prepared, fn (array $row): bool => $row['deleted_at'] !== null)));

        $this->warnDuplicateNames($prepared);

        return self::SUCCESS;
    }

    /**
     * The legacy table had no unique index, so the export can carry two live
     * rows with the same name. Destination::validationRules() rejects that, so
     * an operator has to merge them by hand before either row can be edited.
     *
     * @param  list<array<string, mixed>>  $rows
     */
    private function warnDuplicateNames(array $rows): void
    {
        $live = array_filter($rows, fn (array $row): bool => $row['deleted_at'] === null);

        $groups = [];

        foreach ($live as $row) {
            $groups[mb_strtolower((string) $row['name'])][] = (int) $row['id'];
        }

        $duplicates = array_filter($groups, fn (array $ids): bool => count($ids) > 1);

        if ($duplicates === []) {
            return;
        }

        $this->newLine();
        $this->warn('Duplicate live names (edits will fail validation until merged):');

        foreach ($duplicates as $name => $ids) {
            $this->warn('  '.$name.' — ids '.implode(', ', $ids));
        }
    }

    private function isAbsolutePath(string $path): bool
    {
        return preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1
            || str_starts_with($path, '/')
            || str_starts_with($path, '\\\\');
    }
}
