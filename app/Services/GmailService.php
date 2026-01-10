<?php

namespace App\Services;

use Google\Client;
use Google\Service\Gmail;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class GmailService
{
    protected Client $client;
    protected Gmail $service;
    
    public function __construct()
    {
        $this->client = new Client();
        $this->client->setApplicationName(config('app.name'));
        $this->client->setScopes([
            Gmail::GMAIL_READONLY,
            Gmail::GMAIL_MODIFY,
        ]);
        $this->client->setAuthConfig([
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uris' => [config('services.google.redirect')],
        ]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
    }
    
    /**
     * Set access token for the authenticated user
     */
    public function setAccessToken(User $user): void
    {
        // Check if user has tokens
        if (!$user->google_token) {
            throw new \Exception('No Google OAuth token found. Please sign in with Google.');
        }

        if (!$user->google_refresh_token) {
            throw new \Exception('No refresh token available. Please sign in with Google again to grant offline access.');
        }

        // Always refresh the token to ensure it's valid
        try {
            Log::info("Refreshing token for user {$user->id}");
            Log::info("Refresh token (first 20 chars): " . substr($user->google_refresh_token, 0, 20));
            
            // Refresh the access token
            $newToken = $this->client->fetchAccessTokenWithRefreshToken($user->google_refresh_token);
            
            Log::info("Token response received: " . json_encode(array_keys($newToken)));
            
            // Check for errors
            if (isset($newToken['error'])) {
                Log::error("Token refresh error for user {$user->id}: " . json_encode($newToken));
                throw new \Exception('Token refresh failed: ' . ($newToken['error_description'] ?? $newToken['error']));
            }
            
            // Verify we got an access token
            if (!isset($newToken['access_token'])) {
                Log::error("No access_token in response: " . json_encode($newToken));
                throw new \Exception('Token refresh did not return an access token');
            }
            
            Log::info("New access token (first 20 chars): " . substr($newToken['access_token'], 0, 20));
            
            // Update user's token in database
            $user->update([
                'google_token' => $newToken['access_token'],
                'google_refresh_token' => $newToken['refresh_token'] ?? $user->google_refresh_token,
            ]);
            
            // Important: Set the token on the client
            $this->client->setAccessToken($newToken);
            
            // Verify token is set
            $currentToken = $this->client->getAccessToken();
            Log::info("Token set on client. Has access_token: " . (isset($currentToken['access_token']) ? 'yes' : 'no'));
            
            Log::info("Token refreshed and set successfully for user {$user->id}");
            
        } catch (\Exception $e) {
            Log::error("Token refresh exception for user {$user->id}: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            throw new \Exception('Failed to refresh Google token. Please sign in with Google again.');
        }
        
        // Initialize Gmail service with authenticated client
        $this->service = new Gmail($this->client);
    }
    
    /**
     * Fetch all emails from user's Gmail account
     * 
     * @param int $maxResults Maximum number of emails to fetch
     * @param \DateTimeInterface|null $lastSyncedAt Fetch only emails after this date
     */
    public function fetchEmails(int $maxResults = 100, ?\DateTimeInterface $lastSyncedAt = null): array
    {
        try {
            Log::info("Starting fetchEmails with maxResults={$maxResults}");
            
            // Verify client has a valid token
            $token = $this->client->getAccessToken();
            Log::info("Client token status: " . (isset($token['access_token']) ? 'has token' : 'NO TOKEN'));
            
            $emails = [];
            $pageToken = null;
            $totalFetched = 0;
            
            // Build query for incremental sync
            $query = '';
            if ($lastSyncedAt) {
                // Gmail date format: after:YYYY/MM/DD
                $query = 'after:' . $lastSyncedAt->format('Y/m/d');
            }

            
            do {
                $params = [
                    'maxResults' => min($maxResults - $totalFetched, 100),
                    'pageToken' => $pageToken,
                ];
                
                // Add query parameter for incremental sync
                if ($query) {
                    $params['q'] = $query;
                }

                Log::info("Calling Gmail API with params: " . json_encode($params));
                $messagesResponse = $this->service->users_messages->listUsersMessages('me', $params);
                $messages = $messagesResponse->getMessages();
                
                if (empty($messages)) {
                    break;
                }
                
                foreach ($messages as $message) {
                    $emailData = $this->getEmailDetails($message->getId());
                    if ($emailData) {
                        $emails[] = $emailData;
                        $totalFetched++;
                        
                        if ($totalFetched >= $maxResults) {
                            break 2;
                        }
                    }
                }
                
                $pageToken = $messagesResponse->getNextPageToken();
                
            } while ($pageToken && $totalFetched < $maxResults);
            
            return $emails;
            
        } catch (\Exception $e) {
            Log::error('Gmail fetch error: ' . $e->getMessage());
            throw new \Exception('Failed to fetch emails: ' . $e->getMessage());
        }
    }
    
    /**
     * Get detailed information about a specific email
     */
    public function getEmailDetails(string $messageId): ?array
    {
        try {
            $message = $this->service->users_messages->get('me', $messageId, [
                'format' => 'full'
            ]);
            
            $headers = $message->getPayload()->getHeaders();
            $parts = $message->getPayload()->getParts();
            
            // Extract headers
            $subject = $this->getHeader($headers, 'Subject');
            $from = $this->getHeader($headers, 'From');
            $to = $this->getHeader($headers, 'To');
            $date = $this->getHeader($headers, 'Date');
            
            // Extract body
            $body = $this->getBody($message->getPayload());
            
            // Get labels
            $labels = $message->getLabelIds() ?? [];
            
            return [
                'id' => $message->getId(),
                'thread_id' => $message->getThreadId(),
                'subject' => $subject,
                'from' => $from,
                'to' => $to,
                'date' => $date,
                'body' => $body,
                'snippet' => $message->getSnippet(),
                'labels' => $labels,
                'is_unread' => in_array('UNREAD', $labels),
                'is_starred' => in_array('STARRED', $labels),
                'has_attachments' => $this->hasAttachments($parts),
                'internal_date' => date('Y-m-d H:i:s', $message->getInternalDate() / 1000),
            ];
            
        } catch (\Exception $e) {
            Log::error('Error fetching email details: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Extract header value by name
     */
    protected function getHeader(array $headers, string $name): ?string
    {
        foreach ($headers as $header) {
            if (strtolower($header->getName()) === strtolower($name)) {
                return $header->getValue();
            }
        }
        return null;
    }
    
    /**
     * Extract email body
     */
    protected function getBody($payload): string
    {
        $body = '';
        
        if ($payload->getBody()->getData()) {
            $body = $this->base64UrlDecode($payload->getBody()->getData());
        } elseif ($payload->getParts()) {
            foreach ($payload->getParts() as $part) {
                if ($part->getMimeType() === 'text/plain' || $part->getMimeType() === 'text/html') {
                    if ($part->getBody()->getData()) {
                        $body = $this->base64UrlDecode($part->getBody()->getData());
                        break;
                    }
                }
                
                // Check nested parts
                if ($part->getParts()) {
                    foreach ($part->getParts() as $subPart) {
                        if ($subPart->getBody()->getData()) {
                            $body = $this->base64UrlDecode($subPart->getBody()->getData());
                            break 2;
                        }
                    }
                }
            }
        }
        
        return $body;
    }
    
    /**
     * Check if email has attachments
     */
    protected function hasAttachments(?array $parts): bool
    {
        if (!$parts) {
            return false;
        }
        
        foreach ($parts as $part) {
            if ($part->getFilename() && $part->getBody()->getAttachmentId()) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Decode base64 URL encoded strings
     */
    protected function base64UrlDecode($data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
