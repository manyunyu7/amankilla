<?php

namespace Database\Factories;

use App\Models\Universe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Character>
 */
class CharacterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['INFJ', 'INFP', 'INTJ', 'INTP', 'ENFJ', 'ENFP', 'ENTJ', 'ENTP'];

        return [
            'universe_id' => Universe::factory(),
            'name' => fake()->name(),
            'nickname' => fake()->optional()->firstName(),
            'type' => fake()->randomElement($types),
            'description' => fake()->paragraph(),
            'traits' => fake()->randomElements(['caring', 'analytical', 'creative', 'empathetic', 'logical'], 3),
            'avatar_url' => null,
            'color' => fake()->hexColor(),
        ];
    }

    /**
     * Create an INFJ character.
     */
    public function infj(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'INFJ',
            'traits' => ['empathetic', 'creative', 'idealistic'],
        ]);
    }

    /**
     * Create an INFP character.
     */
    public function infp(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'INFP',
            'traits' => ['creative', 'caring', 'reflective'],
        ]);
    }
}
