<?php

use App\Models\GoogleAccount;
use App\Models\User;
use App\Services\GmailService;
use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\ListMessagesResponse;
use Google\Service\Gmail\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// ============================================
// Test Helper Functions
// ============================================

/**
 * Create a mock Google Client with token refresh capability
 */
function mockGoogleClient(bool $refreshSuccess = true, ?string $newToken = 'new-access-token'): Client
{
    $clientMock = Mockery::mock(Client::class);
    
    $clientMock->shouldReceive('setApplicationName')->andReturnSelf();
    $clientMock->shouldReceive('setScopes')->andReturnSelf();
    $clientMock->shouldReceive('setAuthConfig')->andReturnSelf();
    $clientMock->shouldReceive('setAccessType')->andReturnSelf();
    $clientMock->shouldReceive('setPrompt')->andReturnSelf();
    
    if ($refreshSuccess) {
        $clientMock->shouldReceive('fetchAccessTokenWithRefreshToken')
            ->andReturn([
                'access_token' => $newToken,
                'expires_in' => 3600,
                'token_type' => 'Bearer',
            ]);
        
        $clientMock->shouldReceive('setAccessToken')->andReturnSelf();
        $clientMock->shouldReceive('getAccessToken')
            ->andReturn(['access_token' => $newToken]);
    } else {
        $clientMock->shouldReceive('fetchAccessTokenWithRefreshToken')
            ->andReturn([
                'error' => 'invalid_grant',
                'error_description' => 'Token has been expired or revoked',
            ]);
    }
    
    return $clientMock;
}

/**
 * Create a mock Gmail Message object
 */
function mockGmailMessage(
    string $id = 'msg-123',
    string $threadId = 'thread-123',
    string $subject = 'Test Email',
    string $from = 'sender@example.com',
    array $labels = ['INBOX', 'UNREAD']
): Message
{
    $message = Mockery::mock(Message::class);
    $message->shouldReceive('getId')->andReturn($id);
    $message->shouldReceive('getThreadId')->andReturn($threadId);
    $message->shouldReceive('getSnippet')->andReturn('This is a test email snippet');
    $message->shouldReceive('getLabelIds')->andReturn($labels);
    $message->shouldReceive('getInternalDate')->andReturn(time() * 1000);
    
    // Mock payload for headers
    $payload = Mockery::mock();
    
    // Mock headers
    $headers = [
        mockHeader('Subject', $subject),
        mockHeader('From', $from),
        mockHeader('To', 'recipient@example.com'),
        mockHeader('Date', date('Y-m-d H:i:s')),
    ];
    $payload->shouldReceive('getHeaders')->andReturn($headers);
    
    // Mock body
    $body = Mockery::mock();
    $body->shouldReceive('getData')->andReturn(base64_encode('This is the email body content'));
    $payload->shouldReceive('getBody')->andReturn($body);
    $payload->shouldReceive('getParts')->andReturn(null);
    
    $message->shouldReceive('getPayload')->andReturn($payload);
    
    return $message;
}

/**
 * Create a mock Gmail header
 */
function mockHeader(string $name, string $value): object
{
    $header = Mockery::mock();
    $header->shouldReceive('getName')->andReturn($name);
    $header->shouldReceive('getValue')->andReturn($value);
    return $header;
}

/**
 * Create a mock Gmail service
 */
function mockGmailService(): Gmail
{
    return Mockery::mock(Gmail::class);
}

// ============================================
// Tests
// ============================================

it('refreshes access token successfully and updates Google account', function () {
    // Arrange
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create([
        'user_id' => $user->id,
        'google_token' => 'old-access-token',
        'google_refresh_token' => 'refresh-token-123',
    ]);

    $clientMock = mockGoogleClient(true, 'new-refreshed-token');
    
    // Create a partial mock of GmailService to inject our mock client
    $service = Mockery::mock(GmailService::class)->makePartial();
    $service->shouldAllowMockingProtectedMethods();
    
    // Replace the client with our mock
    $reflection = new ReflectionClass($service);
    $clientProperty = $reflection->getProperty('client');
    $clientProperty->setAccessible(true);
    $clientProperty->setValue($service, $clientMock);

    // Act
    $service->setAccessToken($googleAccount);

    // Assert
    $googleAccount->refresh();
    expect($googleAccount->google_token)->toBe('new-refreshed-token');
});

it('throws exception when Google account has no refresh token', function () {
    // Arrange
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create([
        'user_id' => $user->id,
        'google_token' => 'access-token',
        'google_refresh_token' => 'dummy-refresh-token', // Has refresh token
    ]);

    // Manually set refresh token to null using reflection to simulate edge case
    $reflection = new \ReflectionClass($googleAccount);
    $property = $reflection->getProperty('attributes');
    $property->setAccessible(true);
    $attributes = $property->getValue($googleAccount);
    $attributes['google_refresh_token'] = null;
    $property->setValue($googleAccount, $attributes);

    $service = new GmailService();

    // Act & Assert
    expect(fn() => $service->setAccessToken($googleAccount))
        ->toThrow(\Exception::class, 'No refresh token available');
});

it('throws exception when token refresh fails', function () {
    // Arrange
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create([
        'user_id' => $user->id,
        'google_token' => 'old-token',
        'google_refresh_token' => 'expired-refresh-token',
    ]);

    $clientMock = mockGoogleClient(false);
    
    $service = Mockery::mock(GmailService::class)->makePartial();
    $service->shouldAllowMockingProtectedMethods();
    
    $reflection = new ReflectionClass($service);
    $clientProperty = $reflection->getProperty('client');
    $clientProperty->setAccessible(true);
    $clientProperty->setValue($service, $clientMock);

    // Act & Assert
    expect(fn() => $service->setAccessToken($googleAccount))
        ->toThrow(\Exception::class);
});

it('fetches emails with pagination', function () {
    // Arrange
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create(['user_id' => $user->id]);

    // Mock Gmail service
    $gmailServiceMock = mockGmailService();
    
    // Mock users_messages resource
    $usersMessagesMock = Mockery::mock();
    
    // Mock first page response
    $message1 = Mockery::mock();
    $message1->shouldReceive('getId')->andReturn('msg-1');
    
    $message2 = Mockery::mock();
    $message2->shouldReceive('getId')->andReturn('msg-2');
    
    $firstPageResponse = Mockery::mock(ListMessagesResponse::class);
    $firstPageResponse->shouldReceive('getMessages')->andReturn([$message1, $message2]);
    $firstPageResponse->shouldReceive('getNextPageToken')->andReturn('page-token-2');
    
    // Mock second page response
    $message3 = Mockery::mock();
    $message3->shouldReceive('getId')->andReturn('msg-3');
    
    $secondPageResponse = Mockery::mock(ListMessagesResponse::class);
    $secondPageResponse->shouldReceive('getMessages')->andReturn([$message3]);
    $secondPageResponse->shouldReceive('getNextPageToken')->andReturn(null);
    
    $usersMessagesMock->shouldReceive('listUsersMessages')
        ->twice()
        ->andReturn($firstPageResponse, $secondPageResponse);
    
    // Mock getEmailDetails calls
    $usersMessagesMock->shouldReceive('get')
        ->times(3)
        ->andReturnUsing(function ($userId, $messageId) {
            return mockGmailMessage($messageId, "thread-{$messageId}", "Subject {$messageId}");
        });
    
    $gmailServiceMock->users_messages = $usersMessagesMock;
    
    // Create service with mocked dependencies
    $service = Mockery::mock(GmailService::class)->makePartial();
    $service->shouldAllowMockingProtectedMethods();
    
    $reflection = new ReflectionClass($service);
    $serviceProperty = $reflection->getProperty('service');
    $serviceProperty->setAccessible(true);
    $serviceProperty->setValue($service, $gmailServiceMock);
    
    $clientProperty = $reflection->getProperty('client');
    $clientProperty->setAccessible(true);
    $clientMock = mockGoogleClient();
    $clientProperty->setValue($service, $clientMock);

    // Act
    $emails = $service->fetchEmails(10);

    // Assert
    expect($emails)->toBeArray()
        ->and($emails)->toHaveCount(3)
        ->and($emails[0]['id'])->toBe('msg-1')
        ->and($emails[1]['id'])->toBe('msg-2')
        ->and($emails[2]['id'])->toBe('msg-3');
});

it('fetches emails with date filter for incremental sync', function () {
    // Arrange
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create(['user_id' => $user->id]);
    $lastSyncDate = now()->subDays(7);

    $gmailServiceMock = mockGmailService();
    $usersMessagesMock = Mockery::mock();
    
    $message = Mockery::mock();
    $message->shouldReceive('getId')->andReturn('msg-new');
    
    $response = Mockery::mock(ListMessagesResponse::class);
    $response->shouldReceive('getMessages')->andReturn([$message]);
    $response->shouldReceive('getNextPageToken')->andReturn(null);
    
    // Verify the query parameter includes the date filter
    $usersMessagesMock->shouldReceive('listUsersMessages')
        ->once()
        ->withArgs(function ($userId, $params) use ($lastSyncDate) {
            return isset($params['q']) && 
                   str_contains($params['q'], 'after:' . $lastSyncDate->format('Y/m/d'));
        })
        ->andReturn($response);
    
    $usersMessagesMock->shouldReceive('get')
        ->once()
        ->andReturn(mockGmailMessage('msg-new'));
    
    $gmailServiceMock->users_messages = $usersMessagesMock;
    
    $service = Mockery::mock(GmailService::class)->makePartial();
    $reflection = new ReflectionClass($service);
    
    $serviceProperty = $reflection->getProperty('service');
    $serviceProperty->setAccessible(true);
    $serviceProperty->setValue($service, $gmailServiceMock);
    
    $clientProperty = $reflection->getProperty('client');
    $clientProperty->setAccessible(true);
    $clientProperty->setValue($service, mockGoogleClient());

    // Act
    $emails = $service->fetchEmails(10, $lastSyncDate);

    // Assert
    expect($emails)->toBeArray()
        ->and($emails)->toHaveCount(1)
        ->and($emails[0]['id'])->toBe('msg-new');
});

it('returns empty array when no emails found', function () {
    // Arrange
    $gmailServiceMock = mockGmailService();
    $usersMessagesMock = Mockery::mock();
    
    $emptyResponse = Mockery::mock(ListMessagesResponse::class);
    $emptyResponse->shouldReceive('getMessages')->andReturn([]);
    
    $usersMessagesMock->shouldReceive('listUsersMessages')
        ->once()
        ->andReturn($emptyResponse);
    
    $gmailServiceMock->users_messages = $usersMessagesMock;
    
    $service = Mockery::mock(GmailService::class)->makePartial();
    $reflection = new ReflectionClass($service);
    
    $serviceProperty = $reflection->getProperty('service');
    $serviceProperty->setAccessible(true);
    $serviceProperty->setValue($service, $gmailServiceMock);
    
    $clientProperty = $reflection->getProperty('client');
    $clientProperty->setAccessible(true);
    $clientProperty->setValue($service, mockGoogleClient());

    // Act
    $emails = $service->fetchEmails(10);

    // Assert
    expect($emails)->toBeArray()
        ->and($emails)->toBeEmpty();
});

it('archives email by removing INBOX label', function () {
    // Arrange
    $messageId = 'msg-archive-123';
    
    $gmailServiceMock = mockGmailService();
    $usersMessagesMock = Mockery::mock();
    
    // Expect modify to be called with correct parameters
    $usersMessagesMock->shouldReceive('modify')
        ->once()
        ->withArgs(function ($userId, $msgId, $modifyRequest) use ($messageId) {
            return $userId === 'me' && 
                   $msgId === $messageId &&
                   $modifyRequest->getRemoveLabelIds() === ['INBOX'];
        })
        ->andReturn(true);
    
    $gmailServiceMock->users_messages = $usersMessagesMock;
    
    $service = Mockery::mock(GmailService::class)->makePartial();
    $reflection = new ReflectionClass($service);
    
    $serviceProperty = $reflection->getProperty('service');
    $serviceProperty->setAccessible(true);
    $serviceProperty->setValue($service, $gmailServiceMock);

    // Act
    $result = $service->archiveEmail($messageId);

    // Assert
    expect($result)->toBeTrue();
});

it('returns false when archive fails', function () {
    // Arrange
    $messageId = 'msg-fail-123';
    
    $gmailServiceMock = mockGmailService();
    $usersMessagesMock = Mockery::mock();
    
    // Mock modify to throw exception
    $usersMessagesMock->shouldReceive('modify')
        ->once()
        ->andThrow(new \Exception('Gmail API error'));
    
    $gmailServiceMock->users_messages = $usersMessagesMock;
    
    $service = Mockery::mock(GmailService::class)->makePartial();
    $reflection = new ReflectionClass($service);
    
    $serviceProperty = $reflection->getProperty('service');
    $serviceProperty->setAccessible(true);
    $serviceProperty->setValue($service, $gmailServiceMock);

    // Act
    $result = $service->archiveEmail($messageId);

    // Assert
    expect($result)->toBeFalse();
});

it('decodes base64 URL encoded email body correctly', function () {
    // Arrange
    $service = new GmailService();
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('base64UrlDecode');
    $method->setAccessible(true);

    // Base64 URL encoded "Hello World!"
    $encoded = 'SGVsbG8gV29ybGQh';
    
    // Act
    $decoded = $method->invoke($service, $encoded);

    // Assert
    expect($decoded)->toBe('Hello World!');
});
