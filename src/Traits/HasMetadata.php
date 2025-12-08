<?php

namespace Appleton\OrganisationalUnit\Traits;

use Appleton\OrganisationalUnit\Models\ModelMetadata;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMetadata
{
    public function metadata(): MorphMany
    {
        return $this->morphMany(ModelMetadata::class, 'metadatable');
    }

    public function getMeta(string $key, $default = null)
    {
        $meta = $this->relationLoaded('metadata')
            ? $this->metadata->firstWhere('key', $key)
            : $this->metadata()->where('key', $key)->first();

        if ($meta === null) {
            return $default;
        }

        return $this->extractMetaValue($meta);
    }

    public function setMeta(string $key, $value): self
    {
        $meta = $this->metadata()->firstOrNew(['key' => $key]);

        $meta->string_value = null;
        $meta->int_value = null;
        $meta->decimal_value = null;
        $meta->bool_value = null;
        $meta->json_value = null;

        if (is_bool($value)) {
            $meta->bool_value = $value;
        } elseif (is_int($value) === true) {
            $meta->int_value = $value;
        } elseif (is_float($value) === true) {
            $meta->decimal_value = $value;
        } elseif (is_array($value) === true) {
            $meta->json_value = $value;
        } elseif ($value !== null) {
            $meta->string_value = (string)$value;
        }

        $meta->save();

        if ($this->relationLoaded('metadata')) {
            $this->load('metadata');
        }

        return $this;
    }

    public function forgetMeta(string $key): self
    {
        $this->metadata()->where('key', $key)->delete();

        if ($this->relationLoaded('metadata')) {
            $this->setRelation(
                'metadata',
                $this->metadata->reject(fn($m) => $m->key === $key),
            );
        }

        return $this;
    }

    protected function extractMetaValue(ModelMetadata $meta)
    {
        foreach (['bool_value', 'int_value', 'decimal_value', 'string_value', 'json_value'] as $field) {
            if ($meta->{$field} !== null) {
                return $meta->{$field};
            }
        }

        return null;
    }

}