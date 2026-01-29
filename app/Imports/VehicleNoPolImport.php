<?php
namespace App\Imports;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class VehicleNoPolImport implements ToModel, WithHeadingRow
{
    private $nopolSuccess = [];
    private $nopolDuplicate = [];

    public function model(array $row)
    {
        $nopol = $row['nopol'];
        $existing = DB::table('vehicles')->where('plat_new', $nopol)->first();

        if ($existing) {
            $this->nopolDuplicate[] = $nopol;
        } else {
            $data = [
                'type' => $row['type'],
                'entry_year' => '2024',
                'mileage' => '0',
                'plat_new' => $nopol,
                'plat_old' => null,
                'status' => $row['status'] != '-' ? $row['status'] : 'PASSIVE',
                'desc' => $row['explanation'],
                'mtc' => 'NO',
                'isprivate' => $row['privacy'] === '-' && $row['privacy'] === 'FORALL' ? 1 : 0,
            ];

            DB::table('vehicles')->insert($data);

            $this->nopolSuccess[] = $nopol;
        }
    }

    public function getSuccess()
    {
        return $this->nopolSuccess;
    }

    public function getDuplicate()
    {
        return $this->nopolDuplicate;
    }
}
