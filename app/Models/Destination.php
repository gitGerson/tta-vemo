<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class Destination extends Model
{
    use HasFactory;

    protected $table = 'indonesia_cities';

    protected $fillable = [
        'code',
        'province_code',
        'name',
        'meta',
    ];

    public function getDestinationAttribute(): string
    {
        return $this->name;
    }

    public function getDescAttribute(): ?string
    {
        return $this->meta;
    }

    public static function validationRules(): array
    {
        return [
            'code' => ['required', 'string', 'size:4', Rule::unique('indonesia_cities', 'code')],
            'province_code' => ['required', 'string', 'size:2', Rule::exists('indonesia_provinces', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'meta' => ['nullable', 'string'],
        ];
    }
}
