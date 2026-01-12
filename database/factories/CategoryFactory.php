<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Important', 'Social', 'Promotions', 'Updates', 'Forums']),
            'description' => fake()->sentence(),
            'priority' => fake()->numberBetween(Category::PRIORITY_LOW, Category::PRIORITY_HIGH),
            'archive_after_processing' => fake()->boolean(30), // 30% chance of being true
        ];
    }

    /**
     * Indicate that the category should archive emails after processing.
     */
    public function archiveAfterProcessing(): static
    {
        return $this->state(fn (array $attributes) => [
            'archive_after_processing' => true,
        ]);
    }

    /**
     * Indicate that the category should NOT archive emails after processing.
     */
    public function noArchive(): static
    {
        return $this->state(fn (array $attributes) => [
            'archive_after_processing' => false,
        ]);
    }

    /**
     * Set a high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => Category::PRIORITY_HIGH,
        ]);
    }
}
