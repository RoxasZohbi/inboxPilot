<?php

use App\Jobs\ProcessEmailJob;
use App\Jobs\ProcessEmailWithAIJob;
use App\Jobs\SyncGmailEmailsJob;
use App\Models\Email;
use App\Models\GoogleAccount;
use App\Models\User;
use App\Services\GmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

it('syncs emails from Gmail and dispatches ProcessEmailJob for each email', function () {
    // Arrange
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create(['user_id' => $user->id]);
    
    $mockEmails = [
        [
            'id' => 'msg-001',
            'thread_id' => 'thread-001',
            'subject' => 'Test Email 1',
            'from' => 'John Doe <john@example.com>',
            'to' => 'user@test.com',
            'date' => '2026-01-12 10:00:00',
            'body' => 'This is test email body 1',
            'snippet' => 'This is test email...',
            'labels' => ['INBOX', 'UNREAD'],
            'is_unread' => true,
            'is_starred' => false,
            'has_attachments' => false,
            'internal_date' => now()->timestamp,
        ],
        [
            'id' => 'msg-002',
            'thread_id' => 'thread-002',
            'subject' => 'Test Email 2',
            'from' => 'Jane Smith <jane@example.com>',
            'to' => 'user@test.com',
            'date' => '2026-01-12 11:00:00',
            'body' => 'This is test email body 2',
            'snippet' => 'This is test email 2...',
            'labels' => ['INBOX'],
            'is_unread' => false,
            'is_starred' => true,
            'has_attachments' => true,
            'internal_date' => now()->timestamp,
        ],
    ];

    // Mock GmailService
    $gmailService = Mockery::mock(GmailService::class);
    $gmailService->shouldReceive('setAccessToken')
        ->once()
        ->with($googleAccount);
    $gmailService->shouldReceive('fetchEmails')
        ->once()
        ->with(100, Mockery::type('DateTimeInterface'))
        ->andReturn($mockEmails);
    
    app()->instance(GmailService::class, $gmailService);

    // Act - explicitly set maxResults to 100
    $job = new SyncGmailEmailsJob($googleAccount, 100);
    $job->handle($gmailService);

    // Assert
    Queue::assertPushed(ProcessEmailJob::class, 2);
    Queue::assertPushed(ProcessEmailJob::class, function ($job) use ($mockEmails, $googleAccount) {
        return $job->googleAccount->id === $googleAccount->id
            && in_array($job->emailData['id'], array_column($mockEmails, 'id'));
    });

    // Verify cache was updated
    $cacheKey = "gmail_sync:{$googleAccount->user_id}:{$googleAccount->id}";
    $syncStatus = Cache::get($cacheKey);
    expect($syncStatus)->not->toBeNull()
        ->and($syncStatus['status'])->toBe('processing')
        ->and($syncStatus['total_emails'])->toBe(2)
        ->and($syncStatus['processed'])->toBe(0);
});

it('respects sync limit from config and stops dispatching jobs when limit is reached', function () {
    // Arrange
    Config::set('app.gmail_sync_limit', 2);
    
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create(['user_id' => $user->id]);
    
    // Create 1 existing email (so only 1 more can be added to reach limit of 2)
    Email::factory()->create([
        'user_id' => $user->id,
        'google_account_id' => $googleAccount->id,
    ]);

    $mockEmails = [
        [
            'id' => 'msg-001',
            'thread_id' => 'thread-001',
            'subject' => 'Test Email 1',
            'from' => 'sender1@example.com',
            'to' => 'user@test.com',
            'date' => '2026-01-12 10:00:00',
            'body' => 'Body 1',
            'snippet' => 'Snippet 1',
            'labels' => ['INBOX'],
            'is_unread' => true,
            'is_starred' => false,
            'has_attachments' => false,
            'internal_date' => now()->timestamp,
        ],
        [
            'id' => 'msg-002',
            'thread_id' => 'thread-002',
            'subject' => 'Test Email 2',
            'from' => 'sender2@example.com',
            'to' => 'user@test.com',
            'date' => '2026-01-12 11:00:00',
            'body' => 'Body 2',
            'snippet' => 'Snippet 2',
            'labels' => ['INBOX'],
            'is_unread' => true,
            'is_starred' => false,
            'has_attachments' => false,
            'internal_date' => now()->timestamp,
        ],
        [
            'id' => 'msg-003',
            'thread_id' => 'thread-003',
            'subject' => 'Test Email 3',
            'from' => 'sender3@example.com',
            'to' => 'user@test.com',
            'date' => '2026-01-12 12:00:00',
            'body' => 'Body 3',
            'snippet' => 'Snippet 3',
            'labels' => ['INBOX'],
            'is_unread' => true,
            'is_starred' => false,
            'has_attachments' => false,
            'internal_date' => now()->timestamp,
        ],
    ];

    // Mock GmailService
    $gmailService = Mockery::mock(GmailService::class);
    $gmailService->shouldReceive('setAccessToken')->once();
    $gmailService->shouldReceive('fetchEmails')
        ->once()
        ->andReturn($mockEmails);
    
    app()->instance(GmailService::class, $gmailService);

    // Act
    $job = new SyncGmailEmailsJob($googleAccount);
    $job->handle($gmailService);

    // Assert - should only dispatch 1 job (to reach limit of 2 total emails)
    Queue::assertPushed(ProcessEmailJob::class, 1);
    
    // Verify cache shows correct total
    $cacheKey = "gmail_sync:{$googleAccount->user_id}:{$googleAccount->id}";
    $syncStatus = Cache::get($cacheKey);
    expect($syncStatus['total_emails'])->toBe(1);
});

it('handles empty results and updates last_synced_at without dispatching jobs', function () {
    // Arrange
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create([
        'user_id' => $user->id,
        'last_synced_at' => null,
    ]);

    // Mock GmailService to return empty array
    $gmailService = Mockery::mock(GmailService::class);
    $gmailService->shouldReceive('setAccessToken')->once();
    $gmailService->shouldReceive('fetchEmails')
        ->once()
        ->andReturn([]);
    
    app()->instance(GmailService::class, $gmailService);

    // Act
    $job = new SyncGmailEmailsJob($googleAccount);
    $job->handle($gmailService);

    // Assert
    Queue::assertNothingPushed();
    
    // Verify Google account's last_synced_at was updated
    $googleAccount->refresh();
    expect($googleAccount->last_synced_at)->not->toBeNull();
    
    // Verify cache was cleared
    $cacheKey = "gmail_sync:{$googleAccount->user_id}:{$googleAccount->id}";
    expect(Cache::get($cacheKey))->toBeNull();
    
    // Verify AI processing jobs are dispatched for pending emails if any exist
    Queue::assertPushed(ProcessEmailWithAIJob::class, 0);
});
