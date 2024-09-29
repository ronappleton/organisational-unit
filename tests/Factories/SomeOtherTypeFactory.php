<?php

declare(strict_types=1);

namespace Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Models\SomeOtherType;

class SomeOtherTypeFactory extends Factory
{
    protected $model = SomeOtherType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
