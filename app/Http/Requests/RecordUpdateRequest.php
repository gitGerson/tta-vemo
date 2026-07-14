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
        $rules = Record::validationRules();
        // On edit the start km is always present and explicitly entered.
        $rules['start_km'] = ['required', 'string'];

        return $rules;
    }
}
