<?php
namespace App\Imports;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class VehicleMileageImport implements ToModel, WithHeadingRow
{
    private $updatedCount = 0;

    private $updatedPlates = [];

    private $failedPlates = [];

    /**
     * @param array $row
     *
     * @return void
     */
    public function model(array $row)
    {
        $mbbmasset = DB::table('mbbmasset')
            ->where('ID', $row['idasset'])
            ->first();

        if (!$mbbmasset) {
            Log::warning("mbbmasset not found for ID: {$row['idasset']}");
            return;
        }

        $vehicle = DB::table('vehicles')
            ->where('plat_new', Str::upper($mbbmasset->NOPOL))
            ->first();

        if (!$vehicle) {
            Log::warning('Vehicle not found for plate number: ' . Str::upper($mbbmasset->NOPOL));
            $this->failedPlates[] = Str::upper($mbbmasset->NOPOL);
            return;
        }

        $updated = DB::table('vehicles')
            ->where('id', $vehicle->id)
            ->update([
                'mileage' => $row['end'] ?? $vehicle->mileage,
            ]);

        if ($updated) {
            $this->updatedCount++;
            $this->updatedPlates[] = Str::upper($mbbmasset->NOPOL);
        } else {
            $this->failedPlates[] = Str::upper($mbbmasset->NOPOL);
        }
    }

    /**
     * Get the number of updated records.
     *
     * @return int
     */
    public function getUpdatedCount()
    {
        return $this->updatedCount;
    }

    /**
     * Get the list of updated license plates.
     *
     * @return array
     */
    public function getUpdatedPlates()
    {
        return $this->updatedPlates;
    }

    /**
     * Get the list of failed license plates.
     *
     * @return array
     */
    public function getFailedPlates()
    {
        return $this->failedPlates;
    }
}
