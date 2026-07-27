<?php

namespace Tests\Feature;

use App\Exports\RecordExport;
use App\Models\Company;
use App\Models\Record;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class RecordExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_includes_unit_price_after_quantity_and_blanks_legacy_nulls(): void
    {
        $company = Company::factory()->create(['name' => 'Tong Tji']);

        Record::factory()->create([
            'company_id' => $company->id,
            'date' => '2026-06-01',
            'bbm' => '30.5',
            'fuel_unit_price' => '14750.55',
            'idr' => '449891.78',
        ]);
        Record::factory()->create([
            'company_id' => $company->id,
            'date' => '2026-06-02',
            'fuel_unit_price' => null,
        ]);

        $contents = Excel::raw(
            new RecordExport('2026-06-01', '2026-06-30', $company->id),
            ExcelFormat::XLSX,
        );
        $path = tempnam(sys_get_temp_dir(), 'record_export_');
        file_put_contents($path, $contents);

        try {
            $sheet = IOFactory::load($path)->getActiveSheet();

            $this->assertContains('A1:M1', $sheet->getMergeCells());
            $this->assertSame('Jumlah BBM (Liter)', $sheet->getCell('I6')->getValue());
            $this->assertSame('Harga per Liter', $sheet->getCell('J6')->getValue());
            $this->assertSame('Total Harga', $sheet->getCell('K6')->getValue());
            $this->assertSame('30.5', (string) $sheet->getCell('I7')->getValue());
            $this->assertSame('14750.55', (string) $sheet->getCell('J7')->getValue());
            $this->assertSame('449891.78', (string) $sheet->getCell('K7')->getValue());
            $this->assertNull($sheet->getCell('J8')->getValue());
        } finally {
            unlink($path);
        }
    }
}
