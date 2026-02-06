<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Universe>
 */
class UniverseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->paragraph(),
            'cover_image' => null,
            'is_public' => false,
            'allow_fork' => false,
        ];
    }

    /**
     * Make the universe public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Allow forking of the universe.
     */
    public function forkable(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
            'allow_fork' => true,
        ]);
    }
}
