<?php

namespace App\Jobs;

use App\Models\Email;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessEmailJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 120;
    public $tries = 3;
    public $backoff = [10, 30, 60]; // Exponential backoff in seconds

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public array $emailData
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cacheKey = "gmail_sync:{$this->user->id}";
        
        try {
            // Check if user has reached email limit (MVP safety check)
            $syncLimit = config('app.gmail_sync_limit', 500);
            if ($syncLimit > 0) {
                $currentCount = \App\Models\Email::where('user_id', $this->user->id)->count();
                
                if ($currentCount >= $syncLimit) {
                    Log::info("User {$this->user->id} reached email limit ({$syncLimit}). Skipping email processing.");
                    
                    // Mark sync as completed with limit message
                    $syncStatus = Cache::get($cacheKey, []);
                    $syncStatus['status'] = 'completed';
                    $syncStatus['completed_at'] = now()->toIso8601String();
                    $syncStatus['message'] = "Reached maximum email limit ({$syncLimit} emails for MVP)";
                    Cache::put($cacheKey, $syncStatus, now()->addHours(1));
                    
                    return;
                }
            }
            
            // Extract sender name and email from "from" field
            $fromParts = $this->parseFromField($this->emailData['from']);

            // Create or update email in database
            // Log::info("Processing email {$this->emailData['id']} for user {$this->user->id} ===> " . json_encode($this->emailData));
            $email = Email::firstOrNew([
                'user_id' => $this->user->id,
                'gmail_id' => $this->emailData['id'],
            ]);

            $email->fill([
                'thread_id' => $this->emailData['thread_id'],
                'subject' => $this->emailData['subject'],
                'from_email' => $fromParts['email'],
                'from_name' => $fromParts['name'],
                'to' => $this->emailData['to'],
                'date' => $this->emailData['date'],
                'body' => $this->emailData['body'],
                'snippet' => $this->emailData['snippet'],
                'labels' => $this->emailData['labels'],
                'is_unread' => $this->emailData['is_unread'],
                'is_starred' => $this->emailData['is_starred'],
                'has_attachments' => $this->emailData['has_attachments'],
                'internal_date' => $this->emailData['internal_date'],
            ]);

            $email->save();

            // Update processed count in cache
            $syncStatus = Cache::get($cacheKey, []);
            $syncStatus['processed'] = ($syncStatus['processed'] ?? 0) + 1;
            
            // Check if all emails are processed
            if (isset($syncStatus['total_emails']) && $syncStatus['processed'] >= $syncStatus['total_emails']) {
                $syncStatus['status'] = 'completed';
                $syncStatus['completed_at'] = now()->toIso8601String();
                
                // Update user's last sync time on completion
                $this->user->update(['last_synced_at' => now()]);
            }
            
            Cache::put($cacheKey, $syncStatus, now()->addHours(1));

        } catch (\Exception $e) {
            Log::error("Failed to process email {$this->emailData['id']} for user {$this->user->id}: " . $e->getMessage());
            
            // Update failed count
            $syncStatus = Cache::get($cacheKey, []);
            $syncStatus['failed'] = ($syncStatus['failed'] ?? 0) + 1;
            Cache::put($cacheKey, $syncStatus, now()->addHours(1));

            throw $e;
        }
    }

    /**
     * Parse the "from" field to extract name and email.
     */
    protected function parseFromField(?string $from): array
    {
        if (empty($from)) {
            return ['name' => null, 'email' => null];
        }

        // Pattern: "Name <email@example.com>" or just "email@example.com"
        if (preg_match('/^(.+?)\s*<([^>]+)>$/', $from, $matches)) {
            return [
                'name' => trim($matches[1]),
                'email' => trim($matches[2]),
            ];
        }

        // If no name, just email
        if (filter_var($from, FILTER_VALIDATE_EMAIL)) {
            return [
                'name' => null,
                'email' => $from,
            ];
        }

        // Fallback: treat entire string as name
        return [
            'name' => $from,
            'email' => null,
        ];
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $cacheKey = "gmail_sync:{$this->user->id}";
        
        $syncStatus = Cache::get($cacheKey, []);
        $syncStatus['failed'] = ($syncStatus['failed'] ?? 0) + 1;
        Cache::put($cacheKey, $syncStatus, now()->addHours(1));
    }
}
