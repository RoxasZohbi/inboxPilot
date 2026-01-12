<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UnsubscribeAutomationService
{
    /**
     * Get automation status for a specific email from external API
     * 
     * @param int $emailId The email ID to check automation status for
     * @return array ['status' => string, 'message' => string|null, 'attempted_at' => string|null, 'completed_at' => string|null]
     */
    public function getAutomationStatus(int $emailId): array
    {
        // Check cache first (5 minute TTL)
        $cacheKey = "unsubscribe_automation_{$emailId}";
        
        if (Cache::has($cacheKey)) {
            Log::info("Cache hit for unsubscribe automation status: email {$emailId}");
            return Cache::get($cacheKey);
        }

        try {
            Log::info("Fetching unsubscribe automation status for email {$emailId}");

            $apiUrl = config('services.unsubscribe_api.url');

            // Check if API is configured
            if (empty($apiUrl)) {
                Log::warning("Unsubscribe API not configured properly");
                return $this->getDefaultStatus('unknown', 'API not configured');
            }

            // Fetch all jobs from API
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])
            ->timeout(5) // 5 second timeout
            ->get($apiUrl . '/jobs');

            // Check if request was successful
            if ($response->successful()) {
                $data = $response->json();
                
                // Find job matching this email ID (using item_id)
                $jobs = $data['jobs'] ?? [];
                $matchingJob = collect($jobs)->firstWhere('item_id', $emailId);
                
                if ($matchingJob) {
                    $status = [
                        'status' => $this->normalizeStatus($matchingJob['status'] ?? 'unknown'),
                        'message' => $matchingJob['error_message'] ?? null,
                        'attempted_at' => $matchingJob['started_at'] ?? null,
                        'completed_at' => $matchingJob['completed_at'] ?? null,
                    ];

                    // Cache for 5 minutes
                    Cache::put($cacheKey, $status, now()->addMinutes(5));

                    Log::info("Successfully fetched automation status for email {$emailId}: {$status['status']}");
                    
                    return $status;
                }
                
                // No job found for this email
                return $this->getDefaultStatus('pending', 'No automation job found');
            }

            // API returned error status code
            Log::warning("API returned error for email {$emailId}: {$response->status()}");
            return $this->getDefaultStatus('unavailable', 'Status unavailable');

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error("Connection error fetching automation status for email {$emailId}: {$e->getMessage()}");
            return $this->getDefaultStatus('unavailable', 'Connection failed');
        } catch (\Exception $e) {
            Log::error("Error fetching automation status for email {$emailId}: {$e->getMessage()}");
            return $this->getDefaultStatus('unavailable', 'Status unavailable');
        }
    }

    /**
     * Fetch automation status for multiple emails in batch
     * 
     * @param array $emailIds Array of email IDs
     * @return array Associative array [email_id => status_data]
     */
    public function getBatchAutomationStatus(array $emailIds): array
    {
        if (empty($emailIds)) {
            return [];
        }

        $results = [];
        $uncachedIds = [];

        // Check cache first
        foreach ($emailIds as $emailId) {
            $cacheKey = "unsubscribe_automation_{$emailId}";
            if (Cache::has($cacheKey)) {
                $results[$emailId] = Cache::get($cacheKey);
            } else {
                $uncachedIds[] = $emailId;
            }
        }

        // If all results were cached, return
        if (empty($uncachedIds)) {
            Log::info("All automation statuses found in cache for " . count($emailIds) . " emails");
            return $results;
        }

        try {
            Log::info("Fetching batch automation status for " . count($uncachedIds) . " emails");

            $apiUrl = config('services.unsubscribe_api.url');

            // Check if API is configured
            if (empty($apiUrl)) {
                Log::warning("Unsubscribe API not configured properly");
                foreach ($uncachedIds as $emailId) {
                    $results[$emailId] = $this->getDefaultStatus('unknown', 'API not configured');
                }
                return $results;
            }

            // Fetch all jobs from API
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])
            ->timeout(10) // 10 second timeout for batch
            ->get($apiUrl . '/jobs');

            // Check if request was successful
            if ($response->successful()) {
                $data = $response->json();
                $jobs = $data['jobs'] ?? [];
                
                // Create a map of item_id => job data
                $jobMap = collect($jobs)->keyBy('item_id');

                foreach ($uncachedIds as $emailId) {
                    if ($jobMap->has($emailId)) {
                        $job = $jobMap->get($emailId);
                        $status = [
                            'status' => $this->normalizeStatus($job['status'] ?? 'unknown'),
                            'message' => $job['error_message'] ?? null,
                            'attempted_at' => $job['started_at'] ?? null,
                            'completed_at' => $job['completed_at'] ?? null,
                        ];

                        // Cache for 5 minutes
                        Cache::put("unsubscribe_automation_{$emailId}", $status, now()->addMinutes(5));
                        
                        $results[$emailId] = $status;
                    } else {
                        // No job found for this email
                        $results[$emailId] = $this->getDefaultStatus('pending', 'No automation job found');
                    }
                }

                Log::info("Successfully fetched batch automation status for " . count($uncachedIds) . " emails");
                
                return $results;
            }

            // API returned error - set unavailable for all uncached
            Log::warning("API returned error, marking emails as unavailable");
            foreach ($uncachedIds as $emailId) {
                $results[$emailId] = $this->getDefaultStatus('unavailable', 'Status unavailable');
            }

            return $results;

        } catch (\Exception $e) {
            Log::error("Error fetching batch automation status: {$e->getMessage()}");
            
            // Return default status for all uncached emails
            foreach ($uncachedIds as $emailId) {
                $results[$emailId] = $this->getDefaultStatus('unavailable', 'Status unavailable');
            }
            
            return $results;
        }
    }

    /**
     * Normalize API status to internal status values
     * 
     * @param string $apiStatus
     * @return string
     */
    private function normalizeStatus(string $apiStatus): string
    {
        // API statuses: pending, running, completed, failed
        // Our statuses: pending, processing, completed, failed, unavailable, unknown
        
        return match($apiStatus) {
            'running' => 'processing',
            'pending', 'completed', 'failed' => $apiStatus,
            default => 'unknown',
        };
    }

    /**
     * Get default status structure
     * 
     * @param string $status
     * @param string|null $message
     * @return array
     */
    private function getDefaultStatus(string $status = 'unknown', ?string $message = null): array
    {
        return [
            'status' => $status,
            'message' => $message,
            'attempted_at' => null,
            'completed_at' => null,
        ];
    }

    /**
     * Clear cache for a specific email's automation status
     * 
     * @param int $emailId
     * @return void
     */
    public function clearCache(int $emailId): void
    {
        $cacheKey = "unsubscribe_automation_{$emailId}";
        Cache::forget($cacheKey);
        Log::info("Cleared automation status cache for email {$emailId}");
    }
}
