<?php

namespace Database\Factories;

use App\Models\GoogleAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GoogleAccount>
 */
class GoogleAccountFactory extends Factory
{
    protected $model = GoogleAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'google_id' => fake()->uuid(),
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
            'avatar' => fake()->imageUrl(200, 200, 'people'),
            'google_token' => fake()->sha256(),
            'google_refresh_token' => fake()->sha256(),
            'is_primary' => false,
            'last_synced_at' => null,
        ];
    }

    /**
     * Indicate that the Google account is primary.
     */
    public function primary(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_primary' => true,
        ]);
    }

    /**
     * Indicate that the Google account has been synced recently.
     */
    public function synced(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_synced_at' => now()->subMinutes(fake()->numberBetween(1, 60)),
        ]);
    }
}
