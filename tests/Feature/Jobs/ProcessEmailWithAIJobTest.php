<?php

use App\Jobs\ProcessEmailWithAIJob;
use App\Models\Category;
use App\Models\Email;
use App\Models\GoogleAccount;
use App\Models\User;
use App\Services\GmailService;
use App\Services\OpenAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;

uses(RefreshDatabase::class);

it('successfully processes email with AI and updates all fields', function () {
    // Arrange
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->noArchive()->create(['user_id' => $user->id]);
    
    $email = Email::factory()->pending()->create([
        'user_id' => $user->id,
        'google_account_id' => $googleAccount->id,
        'gmail_id' => 'gmail-123',
        'subject' => 'Newsletter from Example Corp',
        'body' => 'This is a promotional newsletter with updates.',
    ]);

    // Mock OpenAIService
    $openAIService = Mockery::mock(OpenAIService::class);
    $openAIService->shouldReceive('processEmail')
        ->once()
        ->with(Mockery::on(fn($arg) => $arg->id === $email->id))
        ->andReturn([
            'success' => true,
            'category_id' => $category->id,
            'summary' => 'Newsletter with promotional content and updates.',
            'is_unsubscribe_available' => true,
            'unsubscribe_url' => 'https://example.com/unsubscribe',
            'error' => null,
        ]);
    
    app()->instance(OpenAIService::class, $openAIService);

    // Mock GmailService (won't be called since category doesn't have archive enabled)
    $gmailService = Mockery::mock(GmailService::class);
    $gmailService->shouldNotReceive('setAccessToken');
    $gmailService->shouldNotReceive('archiveEmail');
    app()->instance(GmailService::class, $gmailService);

    // Act
    $job = new ProcessEmailWithAIJob($email);
    $job->handle($openAIService, $gmailService);

    // Assert
    $email = $email->fresh();
    
    expect($email->status)->toBe('completed')
        ->and($email->category_id)->toBe($category->id)
        ->and($email->ai_summary)->toBe('Newsletter with promotional content and updates.')
        ->and($email->is_unsubscribe_available)->toBe(1)
        ->and($email->unsubscribe_url)->toBe('https://example.com/unsubscribe')
        ->and($email->processed_at)->not->toBeNull()
        ->and($email->failed_reason)->toBeNull()
        ->and($email->is_archived)->toBe(0);
});

it('archives email in Gmail when category has archive_after_processing enabled', function () {
    // Arrange
    Config::set('app.gmail_auto_archive', true);
    
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->archiveAfterProcessing()->create(['user_id' => $user->id]);
    
    $email = Email::factory()->pending()->create([
        'user_id' => $user->id,
        'google_account_id' => $googleAccount->id,
        'gmail_id' => 'gmail-archive-123',
        'subject' => 'Social Media Update',
        'body' => 'Your friend posted a new photo.',
        'is_archived' => false,
    ]);

    // Mock OpenAIService
    $openAIService = Mockery::mock(OpenAIService::class);
    $openAIService->shouldReceive('processEmail')
        ->once()
        ->andReturn([
            'success' => true,
            'category_id' => $category->id,
            'summary' => 'Social media notification about a friend\'s post.',
            'is_unsubscribe_available' => false,
            'unsubscribe_url' => null,
            'error' => null,
        ]);
    
    app()->instance(OpenAIService::class, $openAIService);

    // Mock GmailService
    $gmailService = Mockery::mock(GmailService::class);
    $gmailService->shouldReceive('setAccessToken')
        ->once()
        ->with(Mockery::on(fn($arg) => $arg->id === $googleAccount->id));
    $gmailService->shouldReceive('archiveEmail')
        ->once()
        ->with('gmail-archive-123')
        ->andReturn(true);
    
    app()->instance(GmailService::class, $gmailService);

    // Act
    $job = new ProcessEmailWithAIJob($email);
    $job->handle($openAIService, $gmailService);

    // Assert
    $email->refresh();
    expect($email->status)->toBe('completed')
        ->and($email->category_id)->toBe($category->id)
        ->and($email->is_archived)->toBe(1)
        ->and($email->processed_at)->not->toBeNull();
});

it('marks email as failed after all retries are exhausted', function () {
    // Arrange
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create(['user_id' => $user->id]);
    
    $email = Email::factory()->pending()->create([
        'user_id' => $user->id,
        'google_account_id' => $googleAccount->id,
        'gmail_id' => 'gmail-fail-123',
        'subject' => 'Test Email',
        'body' => 'Test body',
    ]);

    // Mock OpenAIService to return failure
    $openAIService = Mockery::mock(OpenAIService::class);
    $openAIService->shouldReceive('processEmail')
        ->once()
        ->andReturn([
            'success' => false,
            'category_id' => null,
            'summary' => null,
            'is_unsubscribe_available' => false,
            'unsubscribe_url' => null,
            'error' => 'OpenAI API rate limit exceeded',
        ]);
    
    app()->instance(OpenAIService::class, $openAIService);

    // Mock GmailService (should not be called on failure)
    $gmailService = Mockery::mock(GmailService::class);
    $gmailService->shouldNotReceive('setAccessToken');
    $gmailService->shouldNotReceive('archiveEmail');
    app()->instance(GmailService::class, $gmailService);

    // Act & Assert - expect exception to be thrown for retry
    $job = new ProcessEmailWithAIJob($email);
    
    try {
        $job->handle($openAIService, $gmailService);
        expect(true)->toBeFalse('Expected exception was not thrown');
    } catch (\Exception $e) {
        expect($e->getMessage())->toBe('OpenAI API rate limit exceeded');
    }

    // Email should be marked as pending (for retry)
    $email->refresh();
    expect($email->status)->toBe('pending')
        ->and($email->failed_reason)->toBe('OpenAI API rate limit exceeded')
        ->and($email->processed_at)->toBeNull();

    // Simulate the failed() method being called after all retries
    $exception = new \Exception('OpenAI API rate limit exceeded');
    $job->failed($exception);

    // Now email should be marked as permanently failed
    $email->refresh();
    expect($email->status)->toBe('failed')
        ->and($email->failed_reason)->toContain('Permanently failed after')
        ->and($email->failed_reason)->toContain('OpenAI API rate limit exceeded');
});

it('does not archive email when global auto-archive is disabled', function () {
    // Arrange
    Config::set('app.gmail_auto_archive', false);
    
    $user = User::factory()->create();
    $googleAccount = GoogleAccount::factory()->create(['user_id' => $user->id]);
    $category = Category::factory()->archiveAfterProcessing()->create(['user_id' => $user->id]);
    
    $email = Email::factory()->pending()->create([
        'user_id' => $user->id,
        'google_account_id' => $googleAccount->id,
        'gmail_id' => 'gmail-no-archive-123',
        'is_archived' => false,
    ]);

    // Mock OpenAIService
    $openAIService = Mockery::mock(OpenAIService::class);
    $openAIService->shouldReceive('processEmail')
        ->once()
        ->andReturn([
            'success' => true,
            'category_id' => $category->id,
            'summary' => 'Test summary',
            'is_unsubscribe_available' => false,
            'unsubscribe_url' => null,
            'error' => null,
        ]);
    
    app()->instance(OpenAIService::class, $openAIService);

    // Mock GmailService - should NOT be called
    $gmailService = Mockery::mock(GmailService::class);
    $gmailService->shouldNotReceive('setAccessToken');
    $gmailService->shouldNotReceive('archiveEmail');
    app()->instance(GmailService::class, $gmailService);

    // Act
    $job = new ProcessEmailWithAIJob($email);
    $job->handle($openAIService, $gmailService);

    // Assert
    $email->refresh();
    expect($email->status)->toBe('completed')
        ->and($email->is_archived)->toBe(0); // Should NOT be archived
});
