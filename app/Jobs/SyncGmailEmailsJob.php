<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\GmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncGmailEmailsJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 600; // 10 minutes
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public ?int $maxResults = null
    ) {
        // Use config limit if not specified
        $this->maxResults = $maxResults ?? config('app.gmail_sync_limit', 500);
    }

    /**
     * Execute the job.
     */
    public function handle(GmailService $gmailService): void
    {
        $cacheKey = "gmail_sync:{$this->user->id}";
        
        try {
            // Initialize sync status
            Cache::put($cacheKey, [
                'status' => 'processing',
                'total_emails' => 0,
                'processed' => 0,
                'failed' => 0,
                'started_at' => now()->toIso8601String(),
                'completed_at' => null,
            ], now()->addHours(1));

            // Set access token
            $gmailService->setAccessToken($this->user);

            // Check current email count
            $currentEmailCount = \App\Models\Email::where('user_id', $this->user->id)->count();
            $syncLimit = config('app.gmail_sync_limit', 500);
            
            // Calculate how many more emails we can fetch
            if ($syncLimit > 0) {
                $remainingSlots = $syncLimit - $currentEmailCount;
                
                if ($remainingSlots <= 0) {
                    Log::info("User {$this->user->id} already has {$currentEmailCount} emails (limit: {$syncLimit}). Skipping sync.");
                    
                    Cache::put($cacheKey, [
                        'status' => 'completed',
                        'total_emails' => 0,
                        'processed' => 0,
                        'failed' => 0,
                        'started_at' => now()->toIso8601String(),
                        'completed_at' => now()->toIso8601String(),
                        'message' => "Already at email limit ({$syncLimit} emails)",
                    ], now()->addHours(1));
                    
                    return;
                }
                
                // Adjust maxResults to not exceed limit
                $this->maxResults = min($this->maxResults, $remainingSlots);
            }
            
            // Fetch emails (will use incremental sync if last_synced_at is set)
            Log::info("Starting Gmail sync for user {$this->user->id}. Current: {$currentEmailCount}, Limit: {$syncLimit}, Will fetch: {$this->maxResults}");
            $emails = $gmailService->fetchEmails($this->maxResults, $this->user->last_synced_at);
            
            Log::info("Fetched " . count($emails) . " emails for user {$this->user->id}");

            // Update total count
            Cache::put($cacheKey, array_merge(Cache::get($cacheKey, []), [
                'total_emails' => count($emails),
            ]), now()->addHours(1));

            // Dispatch individual processing jobs with rate limiting
            foreach ($emails as $index => $emailData) {
                ProcessEmailJob::dispatch($this->user, $emailData)
                    ->delay(now()->addSeconds($index * 0.5)); // 0.5 second delay between jobs
            }

            Log::info("Gmail sync initiated for user {$this->user->id}: " . count($emails) . " emails queued");

        } catch (\Exception $e) {
            Log::error("Gmail sync failed for user {$this->user->id}: " . $e->getMessage());
            
            Cache::put($cacheKey, array_merge(Cache::get($cacheKey, []), [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'completed_at' => now()->toIso8601String(),
            ]), now()->addHours(1));

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $cacheKey = "gmail_sync:{$this->user->id}";
        
        Cache::put($cacheKey, array_merge(Cache::get($cacheKey, []), [
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'completed_at' => now()->toIso8601String(),
        ]), now()->addHours(1));
    }
}
