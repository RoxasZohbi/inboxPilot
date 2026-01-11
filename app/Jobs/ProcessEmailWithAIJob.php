<?php

namespace App\Jobs;

use App\Models\Email;
use App\Services\OpenAIService;
use App\Services\GmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessEmailWithAIJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 180;
    public $tries = 3;
    public $backoff = [30, 60, 120]; // Exponential backoff in seconds

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Email $email
    ) {}

    /**
     * Execute the job.
     */
    public function handle(OpenAIService $openAIService, GmailService $gmailService): void
    {
        try {
            Log::info("Starting AI processing for email {$this->email->id}");

            // Update status to processing
            $this->email->update(['status' => 'processing']);

            // Process email with AI
            $result = $openAIService->processEmail($this->email);

            if ($result['success']) {
                // Update email with AI results
                $this->email->update([
                    'category_id' => $result['category_id'],
                    'ai_summary' => $result['summary'],
                    'status' => 'completed',
                    'processed_at' => now(),
                    'failed_reason' => null, // Clear any previous errors
                ]);

                Log::info("Successfully processed email {$this->email->id} with AI", [
                    'category_id' => $result['category_id'],
                    'has_summary' => !empty($result['summary']),
                ]);

                // Archive email in Gmail if enabled
                $this->archiveEmailInGmail($gmailService);
            } else {
                // Mark as pending for retry but log the error
                $this->email->update([
                    'status' => 'pending',
                    'failed_reason' => $result['error'] ?? 'AI processing failed without specific error',
                ]);

                Log::warning("AI processing failed for email {$this->email->id}, marked as pending for retry", [
                    'error' => $result['error'],
                ]);

                // Throw exception to trigger job retry
                throw new \Exception($result['error'] ?? 'AI processing failed');
            }

        } catch (\Exception $e) {
            Log::error("Exception in AI processing for email {$this->email->id}: {$e->getMessage()}");

            // Update email with failure information
            $this->email->update([
                'status' => 'pending',
                'failed_reason' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger job retry
        }
    }

    /**
     * Archive the email in Gmail based on global and category-specific settings
     * 
     * @param GmailService $gmailService
     */
    protected function archiveEmailInGmail(GmailService $gmailService): void
    {
        try {
            // Check if global auto-archive is enabled
            if (!config('app.gmail_auto_archive', false)) {
                Log::info("Global auto-archive is disabled, skipping archive for email {$this->email->id}");
                return;
            }

            // Check if email has a category
            if (!$this->email->category_id) {
                Log::info("Email {$this->email->id} has no category, skipping archive");
                return;
            }

            // Load category with archive setting
            $category = $this->email->category;
            
            if (!$category) {
                Log::warning("Category not found for email {$this->email->id}, skipping archive");
                return;
            }

            // Check if category has archive_after_processing enabled
            if (!$category->archive_after_processing) {
                Log::info("Category {$category->name} does not have archive_after_processing enabled, skipping archive for email {$this->email->id}");
                return;
            }

            Log::info("Archiving email {$this->email->id} (category: {$category->name}) in Gmail");

            // Set access token for the user
            $gmailService->setAccessToken($this->email->user);

            // Archive the email (remove from INBOX)
            $archived = $gmailService->archiveEmail($this->email->gmail_id);

            if ($archived) {
                // Update is_archived status in database
                $this->email->update(['is_archived' => true]);
                Log::info("Email {$this->email->id} archived in Gmail successfully and marked as archived in database");
            } else {
                Log::warning("Failed to archive email {$this->email->id} in Gmail, but AI processing completed");
            }

        } catch (\Exception $e) {
            // Don't fail the job if archiving fails - AI processing is the primary goal
            Log::error("Exception while archiving email {$this->email->id} in Gmail: {$e->getMessage()}");
        }
    }

    /**
     * Handle a job failure after all retries are exhausted.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("AI processing permanently failed for email {$this->email->id} after all retries: {$exception->getMessage()}");

        // Mark as failed after all retries exhausted
        $this->email->update([
            'status' => 'failed',
            'failed_reason' => "Permanently failed after {$this->tries} attempts: {$exception->getMessage()}",
        ]);
    }
}
