<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Email;
use App\Models\GoogleAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Email>
 */
class EmailFactory extends Factory
{
    protected $model = Email::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'google_account_id' => GoogleAccount::factory(),
            'gmail_id' => fake()->uuid(),
            'thread_id' => fake()->uuid(),
            'subject' => fake()->sentence(),
            'from_email' => fake()->safeEmail(),
            'from_name' => fake()->name(),
            'to' => fake()->safeEmail(),
            'date' => fake()->dateTimeThisYear(),
            'body' => fake()->paragraphs(3, true),
            'snippet' => fake()->text(150),
            'labels' => ['INBOX', 'UNREAD'],
            'is_unread' => true,
            'is_starred' => false,
            'is_archived' => false,
            'has_attachments' => false,
            'internal_date' => now()->timestamp,
            'category_id' => null,
            'ai_summary' => null,
            'is_unsubscribe_available' => false,
            'unsubscribe_url' => null,
            'status' => 'pending',
            'processed_at' => null,
            'failed_reason' => null,
        ];
    }

    /**
     * Indicate that the email is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_unread' => true,
            'labels' => array_unique(array_merge($attributes['labels'] ?? [], ['UNREAD'])),
        ]);
    }

    /**
     * Indicate that the email is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_unread' => false,
            'labels' => array_diff($attributes['labels'] ?? [], ['UNREAD']),
        ]);
    }

    /**
     * Indicate that the email is starred.
     */
    public function starred(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_starred' => true,
            'labels' => array_unique(array_merge($attributes['labels'] ?? [], ['STARRED'])),
        ]);
    }

    /**
     * Indicate that the email has been processed by AI.
     */
    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => Category::factory(),
            'ai_summary' => fake()->paragraph(),
            'status' => 'completed',
            'processed_at' => now(),
        ]);
    }

    /**
     * Indicate that the email is pending AI processing.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_at' => null,
        ]);
    }

    /**
     * Indicate that the email processing failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'failed_reason' => fake()->sentence(),
            'processed_at' => null,
        ]);
    }

    /**
     * Indicate that the email has unsubscribe information.
     */
    public function withUnsubscribe(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_unsubscribe_available' => true,
            'unsubscribe_url' => fake()->url(),
        ]);
    }

    /**
     * Indicate that the email has attachments.
     */
    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_attachments' => true,
        ]);
    }

    /**
     * Indicate that the email has been archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_archived' => true,
            'labels' => array_diff($attributes['labels'] ?? [], ['INBOX']),
        ]);
    }
}
