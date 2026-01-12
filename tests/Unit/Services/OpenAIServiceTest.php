<?php

use App\Models\Category;
use App\Models\Email;
use App\Models\User;
use App\Services\OpenAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('categorizes email successfully with valid user categories', function () {
    // Arrange
    $user = User::factory()->create();
    $category1 = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Important',
        'description' => 'Important emails requiring action',
    ]);
    $category2 = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Social',
        'description' => 'Social media notifications',
    ]);
    
    $email = Email::factory()->create([
        'user_id' => $user->id,
        'subject' => 'Your LinkedIn connection request',
        'snippet' => 'John Doe wants to connect with you on LinkedIn',
        'body' => 'You have a new connection request on LinkedIn.',
    ]);

    // Mock OpenAI facade and chat response
    $chatMock = Mockery::mock();
    $chatMock->shouldReceive('create')
        ->once()
        ->andReturn((object)[
            'choices' => [
                (object)[
                    'message' => (object)[
                        'content' => (string)$category2->id,
                    ],
                ],
            ],
        ]);

    $openAIMock = Mockery::mock();
    $openAIMock->shouldReceive('chat')->andReturn($chatMock);
    
    OpenAI::swap($openAIMock);

    // Act
    $service = new OpenAIService();
    $result = $service->categorizeEmail($email);

    // Assert
    expect($result)->toBeArray()
        ->and($result['category_id'])->toBe($category2->id)
        ->and($result['error'])->toBeNull();
});

it('handles user with no categories gracefully', function () {
    // Arrange
    $user = User::factory()->create();
    // No categories created for this user
    
    $email = Email::factory()->create([
        'user_id' => $user->id,
        'subject' => 'Test email',
        'body' => 'Test body',
    ]);

    // Act
    $service = new OpenAIService();
    $result = $service->categorizeEmail($email);

    // Assert
    expect($result)->toBeArray()
        ->and($result['category_id'])->toBeNull()
        ->and($result['error'])->toBe('No categories available for this user');
});

it('generates summary for email with valid content', function () {
    // Arrange
    $user = User::factory()->create();
    $email = Email::factory()->create([
        'user_id' => $user->id,
        'subject' => 'Quarterly Report Ready',
        'from_email' => 'reports@company.com',
        'body' => 'Your Q4 2025 quarterly report is now available. It shows a 15% increase in revenue and highlights key achievements across all departments. Please review by end of week.',
    ]);

    // Mock OpenAI facade
    $chatMock = Mockery::mock();
    $chatMock->shouldReceive('create')
        ->once()
        ->andReturn((object)[
            'choices' => [
                (object)[
                    'message' => (object)[
                        'content' => 'Q4 2025 report available showing 15% revenue increase. Review required by end of week.',
                    ],
                ],
            ],
        ]);

    $openAIMock = Mockery::mock();
    $openAIMock->shouldReceive('chat')->andReturn($chatMock);
    OpenAI::swap($openAIMock);

    // Act
    $service = new OpenAIService();
    $result = $service->generateSummary($email);

    // Assert
    expect($result)->toBeArray()
        ->and($result['summary'])->toContain('Q4')
        ->and($result['summary'])->toContain('revenue')
        ->and($result['error'])->toBeNull();
});

it('handles empty email content when generating summary', function () {
    // Arrange
    $user = User::factory()->create();
    $email = Email::factory()->create([
        'user_id' => $user->id,
        'subject' => 'Empty email',
        'body' => null,
        'snippet' => null,
    ]);

    // Act
    $service = new OpenAIService();
    $result = $service->generateSummary($email);

    // Assert
    expect($result)->toBeArray()
        ->and($result['summary'])->toBeNull()
        ->and($result['error'])->toBe('Email has no content to summarize');
});

it('detects unsubscribe information and validates URL', function () {
    // Arrange
    $user = User::factory()->create();
    $email = Email::factory()->create([
        'user_id' => $user->id,
        'subject' => 'Weekly Newsletter',
        'from_email' => 'newsletter@example.com',
        'body' => 'Here is your weekly newsletter. To unsubscribe, click here: https://example.com/unsubscribe?id=123',
    ]);

    // Mock OpenAI facade with valid JSON
    $chatMock = Mockery::mock();
    $chatMock->shouldReceive('create')
        ->once()
        ->andReturn((object)[
            'choices' => [
                (object)[
                    'message' => (object)[
                        'content' => '{"has_unsubscribe": true, "url": "https://example.com/unsubscribe?id=123"}',
                    ],
                ],
            ],
        ]);

    $openAIMock = Mockery::mock();
    $openAIMock->shouldReceive('chat')->andReturn($chatMock);
    OpenAI::swap($openAIMock);

    // Act
    $service = new OpenAIService();
    $result = $service->detectUnsubscribeInfo($email);

    // Assert
    expect($result)->toBeArray()
        ->and($result['is_unsubscribe_available'])->toBeTrue()
        ->and($result['unsubscribe_url'])->toBe('https://example.com/unsubscribe?id=123')
        ->and($result['error'])->toBeNull();
});

it('processes email with all AI operations orchestrated', function () {
    // Arrange
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Newsletters',
    ]);
    
    $email = Email::factory()->create([
        'user_id' => $user->id,
        'subject' => 'Marketing Newsletter',
        'body' => 'Check out our latest products. Unsubscribe at bottom.',
        'category_id' => null,
        'ai_summary' => null,
        'is_unsubscribe_available' => null,
    ]);

    // Mock three OpenAI calls (summary, categorization, unsubscribe)
    $chatMock = Mockery::mock();
    $chatMock->shouldReceive('create')
        ->times(3)
        ->andReturn(
            (object)['choices' => [(object)['message' => (object)['content' => 'Marketing newsletter promoting new products.']]]],
            (object)['choices' => [(object)['message' => (object)['content' => (string)$category->id]]]],
            (object)['choices' => [(object)['message' => (object)['content' => '{"has_unsubscribe": true, "url": "https://example.com/unsub"}']]]]
        );

    $openAIMock = Mockery::mock();
    $openAIMock->shouldReceive('chat')->andReturn($chatMock);
    OpenAI::swap($openAIMock);

    // Act
    $service = new OpenAIService();
    $result = $service->processEmail($email);

    // Assert
    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['category_id'])->toBe($category->id)
        ->and($result['summary'])->toContain('Marketing')
        ->and($result['is_unsubscribe_available'])->toBeTrue()
        ->and($result['unsubscribe_url'])->toBe('https://example.com/unsub')
        ->and($result['error'])->toBeNull();
});

it('skips AI processing for already processed email', function () {
    // Arrange
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);
    
    $email = Email::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'ai_summary' => 'Already processed summary',
        'is_unsubscribe_available' => 1,
        'unsubscribe_url' => 'https://example.com/unsub',
    ]);

    // OpenAI should NOT be called - no need to mock

    // Act
    $service = new OpenAIService();
    $result = $service->processEmail($email);

    // Assert
    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['category_id'])->toBe($category->id)
        ->and($result['summary'])->toBe('Already processed summary')
        ->and($result['is_unsubscribe_available'])->toBeTrue();
});
