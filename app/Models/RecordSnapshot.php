<?php

namespace App\Models;

use App\Models\Concerns\HasDiffrntHashId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecordSnapshot extends Model
{
    use HasDiffrntHashId;

    protected $table = 'record_snapshots';

    protected $fillable = [
        'record_id',
        'driver_1',
        'driver_2',
        'vehicle_plat',
        'destination',
        'company_name',
        'fuel_type_name',
        'start_km',
        'finish_km',
        'totals',
        'bbm',
        'fuel_unit_price',
        'idr',
        'scale',
        'date',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'fuel_unit_price' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function record(): BelongsTo
    {
        return $this->belongsTo(Record::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    public static function recordFor(Record $record): void
    {
        $record->loadMissing(['company', 'fuelType']);

        self::create([
            'record_id' => $record->getKey(),
            'driver_1' => $record->driver_1,
            'driver_2' => $record->driver_2,
            'vehicle_plat' => $record->no_pol,
            'destination' => $record->destination,
            'company_name' => $record->company?->name,
            'fuel_type_name' => $record->fuelType?->name,
            'start_km' => $record->start_km,
            'finish_km' => $record->finish_km,
            'totals' => $record->totals,
            'bbm' => $record->bbm,
            'fuel_unit_price' => $record->fuel_unit_price,
            'idr' => $record->idr,
            'scale' => $record->scale,
            'date' => $record->date,
            'changed_by' => auth()->id(),
        ]);
    }
}
