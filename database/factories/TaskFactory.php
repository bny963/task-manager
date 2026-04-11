<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement([1, 2, 3]),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
        ];
    }

    /**
     * 優先度を高にする
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 3,
        ]);
    }

    /**
     * 優先度を低にする
     */
    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => 1,
        ]);
    }
}
