<?php

namespace App\Imports;

use App\Models\TypeMaintenances;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TypeMaintenancesImport implements ToModel, WithHeadingRow
{
    /**
     * @return Model|null
     */
    public function model(array $row)
    {
        $name = isset($row['name']) ? Str::upper(trim((string) $row['name'])) : '';

        if ($name === '') {
            return null;
        }

        $existingRecord = TypeMaintenances::where('name', $name)->first();

        if ($existingRecord) {
            return null;
        }

        $data = [
            'name' => $name,
        ];

        return new TypeMaintenances($data);
    }
}
