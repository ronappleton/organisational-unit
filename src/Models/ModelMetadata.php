<?php

declare(strict_types=1);

namespace Appleton\OrganisationalUnit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelMetadata extends Model
{
    protected $table = 'model_metadata';

    protected $fillable = [
        'key',
        'string_value',
        'int_value',
        'decimal_value',
        'bool_value',
        'json_value',
    ];

    protected $casts = [
        'bool_value' => 'bool',
        'json_value' => 'array',
    ];

    public function metadatable(): MorphTo
    {
        return $this->morphTo();
    }
}
