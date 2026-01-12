<?php

namespace App\Services;

use App\Models\Email;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAIService
{
    /**
     * Categorize email using OpenAI and user's existing categories
     * 
     * @param Email $email The email to categorize
     * @return array ['category_id' => int|null, 'error' => string|null]
     */
    public function categorizeEmail(Email $email): array
    {
        try {
            Log::info("Starting AI categorization for email {$email->id}");

            // Get user's categories
            $user = $email->user;
            $categories = $user->categories()->get(['id', 'name', 'description']);

            if ($categories->isEmpty()) {
                Log::warning("No categories found for user {$user->id}. Skipping categorization.");
                return [
                    'category_id' => null,
                    'error' => 'No categories available for this user',
                ];
            }

            // Build categories list for the prompt
            $categoriesList = $categories->map(function ($category) {
                return "- ID: {$category->id}, Name: {$category->name}" . 
                       ($category->description ? ", Description: {$category->description}" : "");
            })->implode("\n");

            // Build the prompt
            $prompt = $this->buildCategorizationPrompt($email, $categoriesList);

            Log::info("Sending categorization request to OpenAI for email {$email->id}");

            // Call OpenAI API
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an email categorization assistant. Analyze the email and respond with ONLY the category ID number that best matches. Do not include any other text or explanation.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.0,
                'top_p' => 1,
                'presence_penalty' => 0,
                'frequency_penalty' => 0,
                'max_tokens' => 10,
            ]);

            $categoryIdText = trim($response->choices[0]->message->content);
            
            Log::info("OpenAI categorization response for email {$email->id}: {$categoryIdText}");

            // Extract numeric ID
            preg_match('/\d+/', $categoryIdText, $matches);
            $categoryId = isset($matches[0]) ? (int)$matches[0] : null;

            // Validate that the category ID exists in user's categories
            if ($categoryId && !$categories->contains('id', $categoryId)) {
                Log::warning("OpenAI returned invalid category ID {$categoryId} for email {$email->id}");
                $categoryId = null;
            }

            return [
                'category_id' => $categoryId,
                'error' => null,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to categorize email {$email->id}: {$e->getMessage()}");
            return [
                'category_id' => null,
                'error' => "Categorization failed: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Generate a summary of the email using OpenAI
     * 
     * @param Email $email The email to summarize
     * @return array ['summary' => string|null, 'error' => string|null]
     */
    public function generateSummary(Email $email): array
    {
        try {
            Log::info("Starting AI summary generation for email {$email->id}");

            // Prepare email content for summarization
            $emailContent = $this->prepareEmailContent($email);

            if (empty($emailContent)) {
                Log::warning("Email {$email->id} has no content to summarize");
                return [
                    'summary' => null,
                    'error' => 'Email has no content to summarize',
                ];
            }

            // Build the prompt
            $prompt = $this->buildSummaryPrompt($email, $emailContent);

            Log::info("Sending summary request to OpenAI for email {$email->id}");

            // Call OpenAI API
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an email summarization assistant. Create concise, informative summaries in 2-3 sentences that capture the key points and purpose of the email.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.3,
                'top_p' => 1,
                'presence_penalty' => 0,
                'frequency_penalty' => 0,
                'max_tokens' => 80,
            ]);

            $summary = trim($response->choices[0]->message->content);
            
            Log::info("OpenAI summary generated for email {$email->id}");

            return [
                'summary' => $summary,
                'error' => null,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to generate summary for email {$email->id}: {$e->getMessage()}");
            return [
                'summary' => null,
                'error' => "Summary generation failed: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Build the categorization prompt
     * 
     * @param Email $email
     * @param string $categoriesList
     * @return string
     */
    protected function buildCategorizationPrompt(Email $email, string $categoriesList): string
    {
        $subject = $email->subject ?? 'No subject';
        $from = $email->from_email ?? 'Unknown sender';
        $snippet = $email->snippet ?? '';
        
        return <<<PROMPT
            Analyze this email and categorize it into one of the available categories.

            Email Details:
            From: {$from}
            Subject: {$subject}
            Preview: {$snippet}

            Available Categories:
            {$categoriesList}

            Respond with ONLY the category ID number that best fits this email.
        PROMPT;
    }

    /**
     * Build the summary prompt
     * 
     * @param Email $email
     * @param string $content
     * @return string
     */
    protected function buildSummaryPrompt(Email $email, string $content): string
    {
        $subject = $email->subject ?? 'No subject';
        $from = $email->from_email ?? 'Unknown sender';
        
        return <<<PROMPT
            Summarize this email in 2-3 concise sentences. Focus on the main purpose, key information, and any action items.

            From: {$from}
            Subject: {$subject}

            Email Content:
            {$content}

            Provide a clear and informative summary:
        PROMPT;
    }

    /**
     * Detect unsubscribe information in email using OpenAI
     * 
     * @param Email $email The email to analyze
     * @return array ['is_unsubscribe_available' => bool, 'unsubscribe_url' => string|null, 'error' => string|null]
     */
    public function detectUnsubscribeInfo(Email $email): array
    {
        try {
            Log::info("Starting unsubscribe detection for email {$email->id}");

            // Prepare email content
            $emailContent = $this->prepareEmailContent($email);

            if (empty($emailContent)) {
                Log::warning("Email {$email->id} has no content to analyze");
                return [
                    'is_unsubscribe_available' => false,
                    'unsubscribe_url' => null,
                    'error' => 'Email has no content to analyze',
                ];
            }

            // Build the prompt
            $prompt = $this->buildUnsubscribeDetectionPrompt($email, $emailContent);

            Log::info("Sending unsubscribe detection request to OpenAI for email {$email->id}");

            // Call OpenAI API
            $response = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an email analysis assistant. Detect if an email contains unsubscribe information. Respond ONLY in this exact JSON format: {"has_unsubscribe": true/false, "url": "URL or null"}. Do not include any other text.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.0,
                'top_p' => 1,
                'presence_penalty' => 0,
                'frequency_penalty' => 0,
                'max_tokens' => 100,
            ]);

            $responseText = trim($response->choices[0]->message->content);
            
            Log::info("OpenAI unsubscribe detection response for email {$email->id}: {$responseText}");

            // Parse JSON response
            $data = json_decode($responseText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to parse OpenAI response for email {$email->id}: {$responseText}");
                return [
                    'is_unsubscribe_available' => false,
                    'unsubscribe_url' => null,
                    'error' => 'Failed to parse AI response',
                ];
            }

            $hasUnsubscribe = $data['has_unsubscribe'] ?? false;
            $unsubscribeUrl = $data['url'] ?? null;

            // Validate URL if present
            if ($unsubscribeUrl && !filter_var($unsubscribeUrl, FILTER_VALIDATE_URL)) {
                Log::warning("Invalid unsubscribe URL detected for email {$email->id}: {$unsubscribeUrl}");
                $unsubscribeUrl = null;
            }

            return [
                'is_unsubscribe_available' => $hasUnsubscribe,
                'unsubscribe_url' => $unsubscribeUrl,
                'error' => null,
            ];

        } catch (\Exception $e) {
            Log::error("Failed to detect unsubscribe info for email {$email->id}: {$e->getMessage()}");
            return [
                'is_unsubscribe_available' => false,
                'unsubscribe_url' => null,
                'error' => "Unsubscribe detection failed: {$e->getMessage()}",
            ];
        }
    }

    /**
     * Build the unsubscribe detection prompt
     * 
     * @param Email $email
     * @param string $content
     * @return string
     */
    protected function buildUnsubscribeDetectionPrompt(Email $email, string $content): string
    {
        $subject = $email->subject ?? 'No subject';
        $from = $email->from_email ?? 'Unknown sender';
        
        return <<<PROMPT
            Analyze this email and detect if it contains unsubscribe information (links, URLs, or instructions to unsubscribe).

            From: {$from}
            Subject: {$subject}

            Email Content:
            {$content}

            Look for:
            - Unsubscribe links or URLs
            - "Unsubscribe" buttons or links
            - List-Unsubscribe headers
            - Instructions to opt-out or stop receiving emails

            Respond in this exact JSON format:
            {"has_unsubscribe": true/false, "url": "the unsubscribe URL or null"}
        PROMPT;
    }

    /**
     * Prepare email content for processing
     * 
     * @param Email $email
     * @return string
     */
    protected function prepareEmailContent(Email $email): string
    {
        // Prefer body over snippet
        $content = $email->body ?? $email->snippet ?? '';
        
        // Limit content length to avoid token limits (roughly 4000 characters)
        if (strlen($content) > 4000) {
            $content = substr($content, 0, 4000) . '...';
        }
        
        return $content;
    }

    /**
     * Process email with AI (categorization, summarization, and unsubscribe detection)
     * 
     * @param Email $email
     * @return array ['success' => bool, 'category_id' => int|null, 'summary' => string|null, 'is_unsubscribe_available' => bool, 'unsubscribe_url' => string|null, 'error' => string|null]
     */
    public function processEmail(Email $email): array
    {
        Log::info("Starting AI processing for email {$email->id}");

        $result = [
            'success' => false,
            'category_id' => null,
            'summary' => null,
            'is_unsubscribe_available' => false,
            'unsubscribe_url' => null,
            'error' => null,
        ];

        // Check if all AI processing is already done
        $hasCategory = $email->category_id !== null;
        $hasSummary = !empty($email->ai_summary);
        $hasUnsubscribeCheck = $email->is_unsubscribe_available !== null;

        if ($hasCategory && $hasSummary && $hasUnsubscribeCheck) {
            Log::info("Email {$email->id} already has all AI data. Skipping processing.");
            return [
                'success' => true,
                'category_id' => $email->category_id,
                'summary' => $email->ai_summary,
                'is_unsubscribe_available' => (bool)$email->is_unsubscribe_available,
                'unsubscribe_url' => $email->unsubscribe_url,
                'error' => null,
            ];
        }

        // Generate summary only if not exists
        if (!$hasSummary) {
            $summaryResult = $this->generateSummary($email);
            $result['summary'] = $summaryResult['summary'];
            $errors[] = $summaryResult['error'];
        } else {
            Log::info("Email {$email->id} already has summary. Skipping.");
            $result['summary'] = $email->ai_summary;
        }

        // Categorize email only if not exists
        if (!$hasCategory) {
            $categoryResult = $this->categorizeEmail($email);
            $result['category_id'] = $categoryResult['category_id'];
            $errors[] = $categoryResult['error'];
        } else {
            Log::info("Email {$email->id} already has category. Skipping.");
            $result['category_id'] = $email->category_id;
        }

        // Detect unsubscribe info only if not checked
        if (!$hasUnsubscribeCheck) {
            $unsubscribeResult = $this->detectUnsubscribeInfo($email);
            $result['is_unsubscribe_available'] = $unsubscribeResult['is_unsubscribe_available'];
            $result['unsubscribe_url'] = $unsubscribeResult['unsubscribe_url'];
            $errors[] = $unsubscribeResult['error'];
        } else {
            Log::info("Email {$email->id} already has unsubscribe check. Skipping.");
            $result['is_unsubscribe_available'] = (bool)$email->is_unsubscribe_available;
            $result['unsubscribe_url'] = $email->unsubscribe_url;
        }

        // Collect errors
        $errors = array_filter($errors ?? []);

        if (!empty($errors)) {
            $result['error'] = implode(' | ', $errors);
        }

        // Mark as successful if at least one operation succeeded
        $result['success'] = ($result['summary'] !== null || $result['category_id'] !== null || $result['is_unsubscribe_available']);

        Log::info("AI processing completed for email {$email->id}", [
            'success' => $result['success'],
            'has_category' => $result['category_id'] !== null,
            'has_summary' => $result['summary'] !== null,
            'has_unsubscribe' => $result['is_unsubscribe_available'],
        ]);

        return $result;
    }
}
