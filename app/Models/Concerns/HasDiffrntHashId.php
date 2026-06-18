<?php

namespace App\Models\Concerns;

use App\Services\DiffrntHashId;

trait HasDiffrntHashId
{
    public function getRouteKey()
    {
        return $this->getHashidAttribute();
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field !== null && $field !== $this->getKeyName()) {
            return parent::resolveRouteBinding($value, $field);
        }

        $id = $this->decodeRouteBindingValue($value);

        if ($id === null) {
            return null;
        }

        return parent::resolveRouteBinding($id, $field);
    }

    public function resolveSoftDeletableRouteBinding($value, $field = null)
    {
        if ($field !== null && $field !== $this->getKeyName()) {
            return parent::resolveSoftDeletableRouteBinding($value, $field);
        }

        $id = $this->decodeRouteBindingValue($value);

        if ($id === null) {
            return null;
        }

        return parent::resolveSoftDeletableRouteBinding($id, $field);
    }

    public function getHashidAttribute(): string
    {
        $key = $this->getKey();

        if ($key === null) {
            return '';
        }

        return app(DiffrntHashId::class)->encode((string) $key, $this->getTable());
    }

    protected function decodeRouteBindingValue($value): ?int
    {
        if (! is_scalar($value)) {
            return null;
        }

        return app(DiffrntHashId::class)->decode((string) $value);
    }
}
