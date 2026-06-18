<?php

namespace App\Http\Requests;

class DestinationUpdateRequest extends DestinationStoreRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('master.destinations.edit') ?? false;
    }
}
