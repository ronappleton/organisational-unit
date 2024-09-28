<?php

namespace Database\Factories;

use Appleton\OrganisationalUnit\Models\OrganisationalUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * OrganisationalUnitFactory
 *
 * @extends Factory<OrganisationalUnit>
 */
class OrganisationalUnitFactory extends Factory
{
    protected $model = OrganisationalUnit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parent_id' => null, // You can set this to a random parent ID if needed
            'entity_id' => $this->faker->unique()->randomNumber(),
            'entity_type' => $this->faker->word(),
        ];
    }

    /**
     * Indicate that the model has a parent.
     */
    public function withParent(OrganisationalUnit $parent): static
    {
        return $this->state([
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Indicate that the model is a root unit.
     */
    public function root(): static
    {
        return $this->state([
            'parent_id' => null,
        ]);
    }

    /**
     * Indicate that the model is a leaf unit (no children).
     */
    public function leaf(): static
    {
        return $this->afterCreating(function (OrganisationalUnit $unit) {
            // Prevent adding children to ensure it's a leaf
        });
    }
}
