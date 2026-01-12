<?php

namespace App\Jobs;

use App\Models\GoogleAccount;
use App\Models\Email;
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
        public GoogleAccount $googleAccount,
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
        $cacheKey = "gmail_sync:{$this->googleAccount->user_id}:{$this->googleAccount->id}";
        
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

            // Set access token for the Google account
            $gmailService->setAccessToken($this->googleAccount);

            // Check current email count for this Google account
            $currentEmailCount = Email::where('google_account_id', $this->googleAccount->id)->count();
            $syncLimit = config('app.gmail_sync_limit', 500);
            
            // Calculate how many more emails we can fetch
            if ($syncLimit > 0) {
                $remainingSlots = $syncLimit - $currentEmailCount;
                
                if ($remainingSlots <= 0) {
                    Log::info("Google account {$this->googleAccount->id} already has {$currentEmailCount} emails (limit: {$syncLimit}). Skipping sync.");
                    
                    Log::info("SyncGmailEmailsJob is completed marking job completed");
                    Cache::forget($cacheKey);
                    
                    // Update Google account's last sync time
                    $this->googleAccount->update(['last_synced_at' => now()]);
                    
                    // Still process pending emails with AI
                    $this->dispatchAIProcessingForPendingEmails();
                    
                    return;
                }
                
                // Adjust maxResults to not exceed limit
                $this->maxResults = min($this->maxResults, $remainingSlots);
            }
            
            // Fetch emails (will use incremental sync if last_synced_at is set)
            Log::info("Starting Gmail sync for Google account {$this->googleAccount->id}. Current: {$currentEmailCount}, Limit: {$syncLimit}, Will fetch: {$this->maxResults}");
            $emails = $gmailService->fetchEmails($this->maxResults, $this->googleAccount->created_at);
            
            if (empty($emails)) {
                Log::info("No new emails to sync for Google account {$this->googleAccount->id}");
                
                Log::info("SyncGmailEmailsJob is completed marking job completed");
                Cache::forget($cacheKey);
                
                // Update Google account's last sync time
                $this->googleAccount->update(['last_synced_at' => now()]);
                
                // Still process pending emails with AI
                $this->dispatchAIProcessingForPendingEmails();
                
                return;
            }
            
            Log::info("Fetched " . count($emails) . " emails for Google account {$this->googleAccount->id}");

            // Determine how many jobs to actually dispatch (respect limit)
            $jobsToDispatch = count($emails);
            if ($syncLimit > 0) {
                $jobsToDispatch = min($jobsToDispatch, $syncLimit - $currentEmailCount);
            }

            // Update cache with actual job count
            Cache::put($cacheKey, array_merge(Cache::get($cacheKey, []), [
                'total_emails' => $jobsToDispatch,
            ]), now()->addMinutes(2));

            // Dispatch individual processing jobs with rate limiting
            // STOP dispatching when we reach the limit
            for ($index = 0; $index < $jobsToDispatch; $index++) {
                ProcessEmailJob::dispatch($this->googleAccount, $emails[$index])
                    ->delay(now()->addSeconds($index * 0.5)); // 0.5 second delay between jobs
            }
            
            if ($jobsToDispatch < count($emails)) {
                Log::info("Stopped dispatching at {$jobsToDispatch} jobs (limit reached) out of " . count($emails) . " fetched emails for Google account {$this->googleAccount->id}");
            }

            Log::info("Gmail sync initiated for Google account {$this->googleAccount->id}: " . count($emails) . " emails queued");

        } catch (\Exception $e) {
            Log::error("Gmail sync failed for Google account {$this->googleAccount->id}: " . $e->getMessage());
            
            Cache::put($cacheKey, array_merge(Cache::get($cacheKey, []), [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'completed_at' => now()->toIso8601String(),
            ]), now()->addMinutes(2));

            throw $e;
        }
    }

    /**
     * Dispatch AI processing jobs for emails that need processing
     */
    protected function dispatchAIProcessingForPendingEmails(): void
    {
        try {
            // Get all emails that need AI processing (pending or null status)
            $pendingEmails = Email::where('google_account_id', $this->googleAccount->id)
                ->where(function ($query) {
                    $query->whereNull('status')
                          ->orWhere('status', 'pending');
                })
                ->whereNull('processed_at')
                ->get();

            Log::info("Dispatching AI processing for {$pendingEmails->count()} pending emails for Google account {$this->googleAccount->id}");

            // Dispatch AI jobs for each pending email
            foreach ($pendingEmails as $email) {
                ProcessEmailWithAIJob::dispatch($email);
            }

        } catch (\Exception $e) {
            Log::error("Failed to dispatch AI processing jobs for Google account {$this->googleAccount->id}: {$e->getMessage()}");
            // Don't throw - this is not critical enough to fail the main job
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $cacheKey = "gmail_sync:{$this->googleAccount->user_id}:{$this->googleAccount->id}";
        
        Cache::put($cacheKey, array_merge(Cache::get($cacheKey, []), [
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'completed_at' => now()->toIso8601String(),
        ]), now()->addMinutes(2));
    }
}
