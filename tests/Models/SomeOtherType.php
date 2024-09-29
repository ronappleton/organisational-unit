<?php

declare(strict_types=1);

namespace Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tests\Factories\SomeOtherTypeFactory;

/**
 * SomeType
 *
 * @property-read int $id
 * @property string $name
 */
class SomeOtherType extends Model
{
    use HasFactory;

    protected static function newFactory(): SomeOtherTypeFactory
    {
        return SomeOtherTypeFactory::new();
    }
}
