<?php

use App\Jobs\ProcessEmailJob;
use App\Jobs\ProcessEmailWithAIJob;
use App\Models\Email;
use App\Models\GoogleAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
});

it('creates new email record with parsed data from Gmail', function () {
    // Arrange
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create(['user_id' => $user->id]);
    
    $emailData = [
        'id' => 'gmail-msg-123',
        'thread_id' => 'gmail-thread-456',
        'subject' => 'Welcome to our service',
        'from' => 'John Doe <john@example.com>',
        'to' => 'user@test.com',
        'date' => '2026-01-12 10:30:00',
        'body' => 'This is the full email body content.',
        'snippet' => 'This is the full email...',
        'labels' => ['INBOX', 'UNREAD', 'IMPORTANT'],
        'is_unread' => true,
        'is_starred' => false,
        'has_attachments' => true,
        'internal_date' => 1736680200,
    ];

    // Set up cache for sync status
    $cacheKey = "gmail_sync:{$googleAccount->user_id}:{$googleAccount->id}";
    Cache::put($cacheKey, [
        'status' => 'processing',
        'total_emails' => 1,
        'processed' => 0,
        'failed' => 0,
    ], now()->addHour());

    // Act
    $job = new ProcessEmailJob($googleAccount, $emailData);
    $job->handle();

    // Assert
    expect(Email::count())->toBe(1);
    
    $email = Email::first();
    expect($email->gmail_id)->toBe('gmail-msg-123')
        ->and($email->thread_id)->toBe('gmail-thread-456')
        ->and($email->subject)->toBe('Welcome to our service')
        ->and($email->from_email)->toBe('john@example.com')
        ->and($email->from_name)->toBe('John Doe')
        ->and($email->to)->toBe('user@test.com')
        ->and($email->body)->toBe('This is the full email body content.')
        ->and($email->is_unread)->toBe(1)
        ->and($email->is_starred)->toBe(0)
        ->and($email->has_attachments)->toBe(1)
        ->and($email->user_id)->toBe($user->id)
        ->and($email->google_account_id)->toBe($googleAccount->id);
});

it('updates cache progress and dispatches ProcessEmailWithAIJob when all emails are processed', function () {
    // Arrange
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create([
        'user_id' => $user->id,
        'last_synced_at' => null,
    ]);
    
    // Create 2 pending emails
    Email::factory()->pending()->create([
        'user_id' => $user->id,
        'google_account_id' => $googleAccount->id,
    ]);
    Email::factory()->pending()->create([
        'user_id' => $user->id,
        'google_account_id' => $googleAccount->id,
    ]);

    $emailData = [
        'id' => 'new-gmail-msg',
        'thread_id' => 'thread-789',
        'subject' => 'Final Email',
        'from' => 'sender@example.com',
        'to' => 'user@test.com',
        'date' => '2026-01-12 10:30:00',
        'body' => 'Final email body',
        'snippet' => 'Final email...',
        'labels' => ['INBOX'],
        'is_unread' => false,
        'is_starred' => false,
        'has_attachments' => false,
        'internal_date' => now()->timestamp,
    ];

    // Set up cache indicating this is the last email
    $cacheKey = "gmail_sync:{$googleAccount->user_id}:{$googleAccount->id}";
    Cache::put($cacheKey, [
        'status' => 'processing',
        'total_emails' => 1,
        'processed' => 0,
        'failed' => 0,
    ], now()->addHour());

    // Act
    $job = new ProcessEmailJob($googleAccount, $emailData);
    $job->handle();

    // Assert
    $syncStatus = Cache::get($cacheKey);
    expect($syncStatus['processed'])->toBe(1)
        ->and($syncStatus['status'])->toBe('completed')
        ->and($syncStatus['completed_at'])->not->toBeNull();

    // Verify Google account's last_synced_at was updated
    $googleAccount->refresh();
    expect($googleAccount->last_synced_at)->not->toBeNull();

    // Verify AI processing jobs were dispatched for all 3 pending emails (2 existing + 1 new)
    Queue::assertPushed(ProcessEmailWithAIJob::class, 3);
});

it('handles malformed from field gracefully', function () {
    // Arrange
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create(['user_id' => $user->id]);
    
    $cacheKey = "gmail_sync:{$googleAccount->user_id}:{$googleAccount->id}";
    Cache::put($cacheKey, [
        'status' => 'processing',
        'total_emails' => 3,
        'processed' => 0,
        'failed' => 0,
    ], now()->addHour());

    // Test case 1: Standard format "Name <email>"
    $emailData1 = [
        'id' => 'msg-1',
        'thread_id' => 'thread-1',
        'subject' => 'Test 1',
        'from' => 'Alice Johnson <alice@example.com>',
        'to' => 'user@test.com',
        'date' => '2026-01-12 10:00:00',
        'body' => 'Body 1',
        'snippet' => 'Snippet 1',
        'labels' => ['INBOX'],
        'is_unread' => true,
        'is_starred' => false,
        'has_attachments' => false,
        'internal_date' => now()->timestamp,
    ];

    // Test case 2: Just email address
    $emailData2 = [
        'id' => 'msg-2',
        'thread_id' => 'thread-2',
        'subject' => 'Test 2',
        'from' => 'bob@example.com',
        'to' => 'user@test.com',
        'date' => '2026-01-12 11:00:00',
        'body' => 'Body 2',
        'snippet' => 'Snippet 2',
        'labels' => ['INBOX'],
        'is_unread' => true,
        'is_starred' => false,
        'has_attachments' => false,
        'internal_date' => now()->timestamp,
    ];

    // Test case 3: Multiple email addresses (take first one)
    $emailData3 = [
        'id' => 'msg-3',
        'thread_id' => 'thread-3',
        'subject' => 'Test 3',
        'from' => 'Charlie Brown <charlie@example.com>',
        'to' => 'user@test.com',
        'date' => '2026-01-12 12:00:00',
        'body' => 'Body 3',
        'snippet' => 'Snippet 3',
        'labels' => ['INBOX'],
        'is_unread' => true,
        'is_starred' => false,
        'has_attachments' => false,
        'internal_date' => now()->timestamp,
    ];

    // Act
    $job1 = new ProcessEmailJob($googleAccount, $emailData1);
    $job1->handle();

    $job2 = new ProcessEmailJob($googleAccount, $emailData2);
    $job2->handle();

    $job3 = new ProcessEmailJob($googleAccount, $emailData3);
    $job3->handle();

    // Assert
    expect(Email::count())->toBe(3);
    
    $email1 = Email::where('gmail_id', 'msg-1')->first();
    expect($email1->from_name)->toBe('Alice Johnson')
        ->and($email1->from_email)->toBe('alice@example.com');
    
    $email2 = Email::where('gmail_id', 'msg-2')->first();
    expect($email2->from_name)->toBeNull()
        ->and($email2->from_email)->toBe('bob@example.com');
    
    $email3 = Email::where('gmail_id', 'msg-3')->first();
    expect($email3->from_name)->toBe('Charlie Brown')
        ->and($email3->from_email)->toBe('charlie@example.com');
    
    // Verify cache was updated correctly
    $syncStatus = Cache::get($cacheKey);
    expect($syncStatus['processed'])->toBe(3)
        ->and($syncStatus['failed'])->toBe(0)
        ->and($syncStatus['status'])->toBe('completed');
});
