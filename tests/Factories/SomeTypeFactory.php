<?php

declare(strict_types=1);

namespace Tests\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Tests\Models\SomeType;

class SomeTypeFactory extends Factory
{
    protected $model = SomeType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
        ];
    }
}
