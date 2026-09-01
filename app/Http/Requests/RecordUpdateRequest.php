<?php

namespace App\Http\Requests;

use App\Models\Record;

class RecordUpdateRequest extends RecordStoreRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('operational.records.edit') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $record = $this->route('record');

        // Passing the record keeps its stored destination valid even when that
        // value predates the destinations master.
        $rules = Record::validationRules($record instanceof Record ? $record : null);
        // On edit the start km is always present and explicitly entered.
        $rules['start_km'] = ['required', 'string'];

        return $rules;
    }
}
