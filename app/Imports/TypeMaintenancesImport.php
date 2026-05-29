<?php

namespace App\Imports;

use App\Models\TypeMaintenances;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TypeMaintenancesImport implements ToModel, WithHeadingRow
{
    /**
     * @return Model|null
     */
    public function model(array $row)
    {

        $existingRecord = TypeMaintenances::where('name', $row['name'])->first();

        if ($existingRecord) {
            return null;
        }

        $data = [
            'name' => $row['name'] ?? '',
        ];

        return new TypeMaintenances($data);
    }
}
