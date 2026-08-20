<?php

namespace Tests\Feature;

use App\Services\LegacyImport\LegacySqlDumpReader;
use App\Services\LegacyImport\LegacyVemoImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegacyVemoImportTest extends TestCase
{
    use RefreshDatabase;

    private function fixturePath(): string
    {
        $sql = <<<'SQL'
-- MySQL dump 10.13  Distrib 10.6, for Linux

DROP TABLE IF EXISTS `company`;
CREATE TABLE `company` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(6) NOT NULL,
  `desc` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `company` VALUES
(1,'Tong Tji Tea Indonesia','TTI','HQ desc',NULL,NULL),
(2,'Cahaya Tirta Rasa','CTR',NULL,NULL,NULL);

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `guid` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_by` bigint(20) unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `users` VALUES
(1,'STAGING PURPOSE','staging@tongtji.com',NULL,'$2y$12$abcdefghijklmnopqrstuv',NULL,NULL,NULL,NULL,'stagingpurpose',NULL,NULL,NULL,NULL,NULL),
(2,'GERSON','gerson@tongtji.com',NULL,'$2y$12$zyxwvutsrqponmlkjihgfe',NULL,NULL,NULL,NULL,'gerson',NULL,1,NULL,NULL,NULL);

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE `vehicles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `entry_year` varchar(5) NOT NULL,
  `mileage` varchar(255) DEFAULT NULL,
  `plat_new` varchar(20) NOT NULL,
  `plat_old` varchar(20) DEFAULT NULL,
  `status` enum('ACTIVE','PASSIVE') NOT NULL,
  `fuel_type` varchar(255) NOT NULL DEFAULT 'BBM',
  `mtc` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `desc` text DEFAULT NULL,
  `isprivate` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `vehicles` VALUES
(100,'GRAND MAX','2024','5000','G 8314 BE','G OLD 1','ACTIVE','BBM','NO','a vehicle',1,NULL,NULL,NULL);

DROP TABLE IF EXISTS `records`;
CREATE TABLE `records` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `driver_1` varchar(255) NOT NULL,
  `driver_2` varchar(255) DEFAULT NULL,
  `vehicle_id` bigint(20) unsigned NOT NULL,
  `no_pol` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `company_id` bigint(20) unsigned NOT NULL,
  `start_km` varchar(255) DEFAULT NULL,
  `finish_km` varchar(255) NOT NULL,
  `totals` varchar(255) NOT NULL,
  `bbm` varchar(255) NOT NULL,
  `idr` varchar(255) NOT NULL,
  `scale` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `records` VALUES
(500,'TARSONO','NONE',100,'G 8314 BE','TEGAL',1,'0','100','100','40','280000','5.39','2024-08-05',NULL,NULL,NULL);

DROP TABLE IF EXISTS `maintenances`;
CREATE TABLE `maintenances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code_mt` varchar(255) NOT NULL,
  `no_pol` varchar(255) NOT NULL,
  `get_request` bigint(20) unsigned NOT NULL,
  `get_approval` bigint(20) unsigned DEFAULT NULL,
  `total_amount` double NOT NULL DEFAULT 0,
  `date_in` date NOT NULL,
  `date_check` date DEFAULT NULL,
  `date_out` date DEFAULT NULL,
  `mileage` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `isServiceRoutine` tinyint(1) NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `viewed_at` timestamp NULL DEFAULT NULL,
  `viewed_by` bigint(20) unsigned DEFAULT NULL,
  `is_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `confirmed_by` bigint(20) unsigned DEFAULT NULL,
  `confirmation_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `requested_by_name` varchar(255) DEFAULT NULL,
  `approved_by_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `maintenances` VALUES
(700,'MT-001','G 8314 BE',2,NULL,980000,'2024-11-14',NULL,NULL,'5000','rem',1,'DRAFT',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,'Gerson','Budi');

DROP TABLE IF EXISTS `maintenances_details`;
CREATE TABLE `maintenances_details` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `maintenance_name` varchar(255) NOT NULL,
  `price` double NOT NULL,
  `qty` varchar(50) DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `material` text DEFAULT NULL,
  `maintenance_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `maintenances_details` VALUES
(900,'MASTER REM',490000,'2',980000.00,NULL,700,NULL,NULL,NULL);

DROP TABLE IF EXISTS `note`;
CREATE TABLE `note` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `maintenances_id` bigint(20) unsigned NOT NULL,
  `image` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `note` VALUES
(300,700,'MT-img.jpeg','https://example.com/x.jpeg',NULL,NULL);

DROP TABLE IF EXISTS `notification_service`;
CREATE TABLE `notification_service` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_pol` varchar(255) NOT NULL,
  `read` tinyint(1) NOT NULL DEFAULT 0,
  `from_km` varchar(255) NOT NULL,
  `to_km` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT INTO `notification_service` VALUES
(4,'B 9822 UCC',1,'198251','208251',NULL,NULL);
SQL;

        $path = tempnam(sys_get_temp_dir(), 'vemo_dump_').'.sql';
        file_put_contents($path, $sql);

        return $path;
    }

    private function import(bool $commit): array
    {
        $path = $this->fixturePath();

        try {
            $dump = app(LegacySqlDumpReader::class)->read($path);

            return app(LegacyVemoImportService::class)->import($dump, commit: $commit);
        } finally {
            @unlink($path);
        }
    }

    public function test_dry_run_persists_nothing(): void
    {
        $result = $this->import(commit: false);

        $this->assertSame('dry-run', $result['mode']);
        $this->assertSame(2, $result['imported']['company']);
        $this->assertSame(0, DB::table('companies')->count());
        $this->assertSame(0, DB::table('vehicles')->count());
        $this->assertSame(0, DB::table('maintenances')->count());
    }

    public function test_commit_preserves_primary_keys_and_relationships(): void
    {
        $result = $this->import(commit: true);

        $this->assertSame('commit', $result['mode']);

        // company -> companies rename, desc -> notes.
        $this->assertDatabaseHas('companies', ['id' => 1, 'name' => 'Tong Tji Tea Indonesia', 'code' => 'TTI', 'notes' => 'HQ desc']);
        $this->assertSame(2, DB::table('companies')->count());

        // users keep ids, bcrypt password, and self-referencing created_by.
        $this->assertDatabaseHas('users', [
            'id' => 2,
            'username' => 'gerson',
            'password' => '$2y$12$zyxwvutsrqponmlkjihgfe',
            'created_by' => 1,
        ]);

        // vehicle keeps id; plat_old dropped; company_id backfilled from records.
        $vehicle = DB::table('vehicles')->find(100);
        $this->assertNotNull($vehicle);
        $this->assertSame('G 8314 BE', $vehicle->plat_new);
        $this->assertSame(1, (int) $vehicle->company_id);

        // record keeps id and points at the preserved vehicle/company ids.
        $this->assertDatabaseHas('records', ['id' => 500, 'vehicle_id' => 100, 'company_id' => 1]);
        $this->assertNull(DB::table('records')->find(500)->fuel_unit_price);

        // maintenance: get_request -> created_by, company_id backfilled via plate.
        $maintenance = DB::table('maintenances')->find(700);
        $this->assertNotNull($maintenance);
        $this->assertSame(2, (int) $maintenance->created_by);
        $this->assertSame(1, (int) $maintenance->company_id);
        $this->assertSame('Gerson', $maintenance->requested_by_name);

        $this->assertDatabaseHas('maintenances_details', ['id' => 900, 'maintenance_id' => 700]);
        $this->assertDatabaseHas('note', ['id' => 300, 'maintenances_id' => 700]);
    }

    public function test_unsupported_tables_are_reported(): void
    {
        $result = $this->import(commit: false);

        $this->assertTrue(
            collect($result['warnings'])->contains(fn (string $w): bool => str_contains($w, 'notification_service')),
            'Expected a warning for the skipped notification_service table.'
        );
    }

    public function test_commit_aborts_when_targets_not_empty(): void
    {
        DB::table('companies')->insert(['name' => 'Existing', 'code' => 'EXI']);

        $this->expectException(\RuntimeException::class);

        $this->import(commit: true);
    }
}
