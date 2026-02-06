<?php

namespace Database\Factories;

use App\Models\Scene;
use App\Models\Universe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Timeline>
 */
class TimelineFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'universe_id' => Universe::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'is_canon' => false,
            'color' => fake()->hexColor(),
            'branch_from_id' => null,
        ];
    }

    /**
     * Mark the timeline as canon.
     */
    public function canon(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_canon' => true,
            'name' => 'Canon',
        ]);
    }

    /**
     * Create a branched timeline.
     */
    public function branchedFrom(Scene $scene): static
    {
        return $this->state(fn (array $attributes) => [
            'universe_id' => $scene->timeline->universe_id,
            'branch_from_id' => $scene->id,
            'name' => 'What if: ' . fake()->words(3, true),
        ]);
    }
}
