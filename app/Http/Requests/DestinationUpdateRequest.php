<?php

namespace App\Http\Requests;

use App\Models\Destination;
use Illuminate\Contracts\Validation\ValidationRule;

class DestinationUpdateRequest extends DestinationStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.destinations.edit') ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return Destination::validationRules($this->destination());
    }

    protected function destination(): ?Destination
    {
        $destination = $this->route('destination');

        if ($destination instanceof Destination) {
            return $destination;
        }

        return Destination::query()->find($destination);
    }
}
