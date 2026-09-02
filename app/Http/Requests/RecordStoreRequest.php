<?php

namespace App\Http\Requests;

use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RecordStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('operational.records.create') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return Record::validationRules();
    }

    public function messages(): array
    {
        return Record::validationMessages();
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    $validator->errors()->has('no_pol')
                    || $validator->errors()->has('start_km')
                    || $validator->errors()->has('finish_km')
                ) {
                    return;
                }

                $submittedStartKm = $this->input('start_km');
                $effectiveStartKm = filled($submittedStartKm)
                    ? (float) $submittedStartKm
                    : (float) (Record::latestForPlate((string) $this->input('no_pol'))?->finish_km ?? 0);

                if ((float) $this->input('finish_km') < $effectiveStartKm) {
                    $validator->errors()->add(
                        'finish_km',
                        Record::validationMessages()['finish_km.gte'],
                    );
                }
            },
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'date' => $this->normalizeDate($this->input('date')),
            'start_km' => $this->normalizeMileage($this->input('start_km')),
            'finish_km' => $this->normalizeMileage($this->input('finish_km')),
            'bbm' => is_string($this->input('bbm')) ? trim($this->input('bbm')) : $this->input('bbm'),
            'fuel_unit_price' => is_string($this->input('fuel_unit_price'))
                ? trim($this->input('fuel_unit_price'))
                : $this->input('fuel_unit_price'),
            'idr' => is_string($this->input('idr')) ? trim($this->input('idr')) : $this->input('idr'),
        ]);
    }

    protected function normalizeMileage(mixed $value): mixed
    {
        return is_string($value) ? trim($value) : $value;
    }

    protected function normalizeDate(mixed $value): mixed
    {
        if (
            ! is_string($value)
            || ! preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)
            || ! Carbon::canBeCreatedFromFormat($value, 'd-m-Y')
        ) {
            return $value;
        }

        return Carbon::createFromFormat('d-m-Y', $value)->toDateString();
    }
}
