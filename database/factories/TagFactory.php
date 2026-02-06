<?php

namespace Database\Factories;

use App\Models\Universe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['emotion', 'event', 'theme', 'character'];

        return [
            'universe_id' => Universe::factory(),
            'name' => fake()->unique()->word(),
            'color' => fake()->hexColor(),
            'category' => fake()->randomElement($categories),
        ];
    }

    /**
     * Create an emotion tag.
     */
    public function emotion(): static
    {
        $emotions = ['happy', 'sad', 'angry', 'scared', 'surprised', 'loving'];

        return $this->state(fn (array $attributes) => [
            'category' => 'emotion',
            'name' => fake()->randomElement($emotions),
        ]);
    }

    /**
     * Create an event tag.
     */
    public function event(): static
    {
        $events = ['milestone', 'conflict', 'resolution', 'flashback', 'dream'];

        return $this->state(fn (array $attributes) => [
            'category' => 'event',
            'name' => fake()->randomElement($events),
        ]);
    }

    /**
     * Create a theme tag.
     */
    public function theme(): static
    {
        $themes = ['love', 'loss', 'growth', 'friendship', 'betrayal'];

        return $this->state(fn (array $attributes) => [
            'category' => 'theme',
            'name' => fake()->randomElement($themes),
        ]);
    }
}
