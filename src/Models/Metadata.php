<?php

declare(strict_types=1);

namespace Appleton\OrganisationalUnit\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Metadata extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'key',
        'value',
        'metadatable',
    ];

    /**
     * Get the owning metadatable model.
     */
    public function metadatable(): MorphTo
    {
        return $this->morphTo();
    }
}
