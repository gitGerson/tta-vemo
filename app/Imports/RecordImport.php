<?php
namespace App\Imports;

use App\Models\Record;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class RecordImport implements ToModel, WithHeadingRow
{
    private $nopolSuccess = [];
    private $nopolSuccessUpdate = [];
    private $nopolDuplicate = [];
    private $nopolFailed = [];

    public function model(array $row)
    {
        try {
            $mbbmasset = DB::table('mbbmasset')
                ->where('ID', $row['idasset'])
                ->orderBy('ID', 'desc')
                ->first();

            if (!$mbbmasset) {
                throw new \Exception('Asset not found for id: ' . $row['idasset']);
            }

            $vehicles = DB::table('vehicles')
                ->where('plat_new', $mbbmasset->NOPOL)
                ->orderBy('ID', 'desc')
                ->first();

            if (!$vehicles) {
                throw new \Exception('Vehicle not found for NOPOL: ' . $mbbmasset->NOPOL);
            }

            $mbbmlocation = DB::table('mbbmlocation')
                ->where('ID', $row['idlocation'])
                ->orderBy('ID', 'desc')
                ->first();

            $idemployee1 = DB::table('mbbmemployee')
                ->where('ID', $row['idemployee1'])
                ->orderBy('ID', 'desc')
                ->first();

            $idemployee2 = DB::table('mbbmemployee')
                ->where('ID', $row['idemployee2'])
                ->orderBy('ID', 'desc')
                ->first();

            $existingRecord = Record::where('no_pol', $vehicles->plat_new)->first();

            if ($existingRecord) {
                $existingRecord->update([
                    'driver_1' => $idemployee1?->NAME,
                    'driver_2' => $idemployee2?->NAME,
                    'vehicle_id' => $vehicles->id,
                    'no_pol' => $vehicles->plat_new,
                    'destination' => $mbbmlocation?->LOCATION,
                    'company_id' => $row['idoffice'] == 1 ? 1 : 2,
                    'start_km' => Str::of($row['first'])->remove('-'),
                    'finish_km' => Str::of($row['end'])->remove('-'),
                    'bbm' => $row['bbm'],
                    'idr' => Str::of($row['idr'])->remove('-'),
                    'totals' => $row['total'],
                    'scale' => $row['scale'],
                    'date' => $row['timestamp'] === '0000-00-00' || empty($row['timestamp']) ? null : $row['timestamp'],
                ]);

                $this->nopolSuccessUpdate[] = $vehicles->plat_new;
            } else {
                Record::create([
                    'driver_1' => $idemployee1?->NAME,
                    'driver_2' => $idemployee2?->NAME,
                    'vehicle_id' => $vehicles->id,
                    'no_pol' => $vehicles->plat_new,
                    'destination' => $mbbmlocation?->LOCATION,
                    'company_id' => $row['idoffice'] == 1 ? 1 : 2,
                    'start_km' => Str::of($row['first'])->remove('-'),
                    'finish_km' => Str::of($row['end'])->remove('-'),
                    'bbm' => $row['bbm'],
                    'idr' => Str::of($row['idr'])->remove('-'),
                    'totals' => $row['total'],
                    'scale' => $row['scale'],
                    'date' => $row['timestamp'] === '0000-00-00' || empty($row['timestamp']) ? null : $row['timestamp'],
                ]);

                $this->nopolSuccess[] = $vehicles->plat_new;
            }
        } catch (\Exception $e) {
            $this->nopolFailed[] = $row; // Menyimpan data yang gagal
            Log::error('Failed to process record: ' . $e->getMessage());
        }
    }

    public function getSuccess()
    {
        return $this->nopolSuccess;
    }

    public function getSuccessUpdate()
    {
        return $this->nopolSuccessUpdate;
    }

    public function getDuplicate()
    {
        return $this->nopolDuplicate;
    }

    public function getFailed()
    {
        return $this->nopolFailed;
    }   
}
