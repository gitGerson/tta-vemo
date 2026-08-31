<?php

namespace Tests\Feature;

use App\Models\Destination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ImportDestinationsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_reports_rows_without_writing(): void
    {
        $this->artisan('destinations:import', ['--file' => $this->fixture()])
            ->expectsOutputToContain('Rows in export: 3')
            ->expectsOutputToContain('Inserted: 3')
            ->assertSuccessful();

        $this->assertDatabaseCount('destinations', 0);
    }

    public function test_commit_preserves_ids_timestamps_and_soft_deletes(): void
    {
        $this->artisan('destinations:import', ['--file' => $this->fixture(), '--commit' => true])
            ->assertSuccessful();

        $this->assertDatabaseHas('destinations', [
            'id' => 3,
            'name' => 'BANDUNG',
            'desc' => 'BANDUNG',
        ]);

        $this->assertDatabaseHas('destinations', [
            'id' => 55,
            'name' => 'Magelang',
            'desc' => null,
        ]);

        $this->assertSoftDeleted('destinations', ['id' => 57, 'name' => 'KETAPANG']);
        $this->assertSame(2, Destination::query()->count());
        $this->assertSame('2026-04-13 18:57:30', Destination::find(55)->created_at->toDateTimeString());
    }

    public function test_commit_is_idempotent(): void
    {
        $this->artisan('destinations:import', ['--file' => $this->fixture(), '--commit' => true])->assertSuccessful();

        $this->artisan('destinations:import', ['--file' => $this->fixture(), '--commit' => true])
            ->expectsOutputToContain('Inserted: 0')
            ->expectsOutputToContain('Updated: 3')
            ->assertSuccessful();

        $this->assertDatabaseCount('destinations', 3);
    }

    public function test_duplicate_live_names_are_reported(): void
    {
        $this->artisan('destinations:import', ['--file' => $this->duplicateFixture()])
            ->expectsOutputToContain('Duplicate live names')
            ->expectsOutputToContain('bogor')
            ->assertSuccessful();
    }

    public function test_missing_file_fails(): void
    {
        $this->artisan('destinations:import', ['--file' => 'database/data/does-not-exist.json'])
            ->assertFailed();
    }

    public function test_real_export_matches_the_command_contract(): void
    {
        $this->artisan('destinations:import', ['--commit' => true])->assertSuccessful();

        $this->assertSame(55, Destination::withTrashed()->count());
        $this->assertSame(2, Destination::onlyTrashed()->count());
    }

    private function fixture(): string
    {
        return $this->writeFixture('destinations-import.json', [
            ['id' => 3, 'name' => 'BANDUNG', 'desc' => 'BANDUNG', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 55, 'name' => 'Magelang', 'desc' => null, 'created_at' => '2026-04-13 18:57:30', 'updated_at' => '2026-04-13 18:57:30', 'deleted_at' => null],
            ['id' => 57, 'name' => 'KETAPANG', 'desc' => 'BANYUWANGI', 'created_at' => '2026-06-23 02:34:07', 'updated_at' => '2026-06-23 02:42:08', 'deleted_at' => '2026-06-23 02:42:08'],
        ]);
    }

    private function duplicateFixture(): string
    {
        return $this->writeFixture('destinations-import-duplicates.json', [
            ['id' => 4, 'name' => 'BOGOR', 'desc' => 'BOGOR', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
            ['id' => 5, 'name' => 'BOGOR', 'desc' => 'SURABAYA', 'created_at' => null, 'updated_at' => null, 'deleted_at' => null],
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function writeFixture(string $name, array $rows): string
    {
        $path = storage_path('framework/testing/'.$name);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode([
            'source' => 'test-fixture',
            'table' => 'destinations',
            'count' => count($rows),
            'rows' => $rows,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return $path;
    }
}
