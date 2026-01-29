<?php

namespace App\Imports;

use App\Models\TypeMaintenances;
use DateTime;
use App\Models\Guru;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;

class TypeMaintenancesImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
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
