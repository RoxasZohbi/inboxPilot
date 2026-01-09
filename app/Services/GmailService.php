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
        $accessToken = [
            'access_token' => $user->google_token,
            'refresh_token' => $user->google_refresh_token ?? null,
            'expires_in' => 3600,
            'created' => time(),
        ];
        
        $this->client->setAccessToken($accessToken);
        
        // Refresh token if expired
        if ($this->client->isAccessTokenExpired()) {
            if ($refreshToken = $user->google_refresh_token) {
                $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                $newToken = $this->client->getAccessToken();
                
                // Update user's token
                $user->update([
                    'google_token' => $newToken['access_token'] ?? null,
                ]);
            }
        }
        
        $this->service = new Gmail($this->client);
    }
    
    /**
     * Fetch all emails from user's Gmail account
     */
    public function fetchEmails(int $maxResults = 100): array
    {
        try {
            $emails = [];
            $pageToken = null;
            $totalFetched = 0;
            
            do {
                $params = [
                    'maxResults' => min($maxResults - $totalFetched, 100),
                    'pageToken' => $pageToken,
                ];
                
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
