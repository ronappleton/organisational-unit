<?php

declare(strict_types=1);

namespace Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tests\Factories\SomeTypeFactory;

/**
 * SomeType
 *
 * @property-read int $id
 * @property string $name
 */
class SomeType extends Model
{
    use HasFactory;

    protected static function newFactory(): SomeTypeFactory
    {
        return SomeTypeFactory::new();
    }
}
