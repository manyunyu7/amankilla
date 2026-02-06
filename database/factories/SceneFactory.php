<?php

namespace Database\Factories;

use App\Models\Timeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Scene>
 */
class SceneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $moods = ['warm', 'tense', 'playful', 'sad', 'romantic', 'neutral'];
        $content = fake()->paragraphs(3, true);

        return [
            'timeline_id' => Timeline::factory(),
            'title' => fake()->sentence(4),
            'content' => $content,
            'summary' => fake()->sentence(),
            'order' => fake()->numberBetween(1, 100),
            'date' => fake()->date(),
            'time' => fake()->time('H:i'),
            'location' => fake()->city(),
            'mood' => fake()->randomElement($moods),
            'pov' => fake()->optional()->firstName(),
            'word_count' => str_word_count($content),
            'is_branch_point' => false,
            'branch_question' => null,
        ];
    }

    /**
     * Make the scene a branch point.
     */
    public function branchPoint(string $question = null): static
    {
        return $this->state(fn (array $attributes) => [
            'is_branch_point' => true,
            'branch_question' => $question ?? 'What if things went differently?',
        ]);
    }

    /**
     * Set a specific mood.
     */
    public function mood(string $mood): static
    {
        return $this->state(fn (array $attributes) => [
            'mood' => $mood,
        ]);
    }

    /**
     * Create a scene with specific order.
     */
    public function withOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order' => $order,
        ]);
    }
}
