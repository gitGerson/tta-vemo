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
        $name = isset($row['nama_jenis_perbaikan']) ? Str::upper(trim((string) $row['nama_jenis_perbaikan'])) : '';

        if ($name === '') {
            return null;
        }

        $existingRecord = TypeMaintenances::where('name', $name)->first();

        if ($existingRecord) {
            return null;
        }

        $jenis = Str::lower(trim((string) ($row['jenis'] ?? '')));

        if (! in_array($jenis, ['jasa', 'barang'], true)) {
            $jenis = 'jasa';
        }

        $data = [
            'name' => $name,
            'jenis' => $jenis,
        ];

        return new TypeMaintenances($data);
    }
}
