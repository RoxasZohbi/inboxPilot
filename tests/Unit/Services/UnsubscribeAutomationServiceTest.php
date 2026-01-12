<?php

use App\Services\UnsubscribeAutomationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    // Set up test configuration
    Config::set('services.unsubscribe_api.url', 'https://api.test.com');
});

it('fetches automation status from API and caches result', function () {
    // Arrange
    Cache::flush();
    
    $emailId = 123;
    $apiResponse = [
        'jobs' => [
            [
                'item_id' => 123,
                'status' => 'completed',
                'error_message' => null,
                'started_at' => '2026-01-12 10:00:00',
                'completed_at' => '2026-01-12 10:05:00',
            ],
            [
                'item_id' => 456,
                'status' => 'failed',
                'error_message' => 'Invalid URL',
                'started_at' => '2026-01-12 09:00:00',
                'completed_at' => '2026-01-12 09:01:00',
            ],
        ],
    ];

    Http::fake([
        'https://api.test.com/jobs' => Http::response($apiResponse, 200),
    ]);

    // Act
    $service = new UnsubscribeAutomationService();
    $result = $service->getAutomationStatus($emailId);

    // Assert
    expect($result)->toBeArray()
        ->and($result['status'])->toBe('completed')
        ->and($result['message'])->toBeNull()
        ->and($result['attempted_at'])->toBe('2026-01-12 10:00:00')
        ->and($result['completed_at'])->toBe('2026-01-12 10:05:00');

    // Verify it was cached
    $cacheKey = "unsubscribe_automation_{$emailId}";
    expect(Cache::has($cacheKey))->toBeTrue();
    expect(Cache::get($cacheKey))->toBe($result);

    // Verify HTTP was called once
    Http::assertSentCount(1);
});

it('returns cached status without making API call', function () {
    // Arrange
    $emailId = 789;
    $cachedStatus = [
        'status' => 'processing',
        'message' => null,
        'attempted_at' => '2026-01-12 11:00:00',
        'completed_at' => null,
    ];

    Cache::put("unsubscribe_automation_{$emailId}", $cachedStatus, now()->addMinutes(5));

    Http::fake(); // Should not be called

    // Act
    $service = new UnsubscribeAutomationService();
    $result = $service->getAutomationStatus($emailId);

    // Assert
    expect($result)->toBe($cachedStatus);
    
    // Verify no HTTP calls were made
    Http::assertNothingSent();
});

it('handles API connection failures gracefully', function () {
    // Arrange
    Cache::flush();
    $emailId = 999;

    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
    });

    // Act
    $service = new UnsubscribeAutomationService();
    $result = $service->getAutomationStatus($emailId);

    // Assert
    expect($result)->toBeArray()
        ->and($result['status'])->toBe('unavailable')
        ->and($result['message'])->toBe('Connection failed')
        ->and($result['attempted_at'])->toBeNull()
        ->and($result['completed_at'])->toBeNull();
});

it('handles API error responses', function () {
    // Arrange
    Cache::flush();
    $emailId = 555;

    Http::fake([
        'https://api.test.com/jobs' => Http::response(['error' => 'Server error'], 500),
    ]);

    // Act
    $service = new UnsubscribeAutomationService();
    $result = $service->getAutomationStatus($emailId);

    // Assert
    expect($result)->toBeArray()
        ->and($result['status'])->toBe('unavailable')
        ->and($result['message'])->toBe('Status unavailable');
});

it('returns pending status when no job found for email', function () {
    // Arrange
    Cache::flush();
    $emailId = 111;

    Http::fake([
        'https://api.test.com/jobs' => Http::response([
            'jobs' => [
                ['item_id' => 222, 'status' => 'completed'],
                ['item_id' => 333, 'status' => 'failed'],
            ],
        ], 200),
    ]);

    // Act
    $service = new UnsubscribeAutomationService();
    $result = $service->getAutomationStatus($emailId);

    // Assert
    expect($result)->toBeArray()
        ->and($result['status'])->toBe('pending')
        ->and($result['message'])->toBe('No automation job found');
});

it('batch fetches automation status with mixed cache hits and misses', function () {
    // Arrange
    Cache::flush();
    
    $emailIds = [100, 200, 300];
    
    // Cache status for email 100
    $cachedStatus = [
        'status' => 'completed',
        'message' => null,
        'attempted_at' => '2026-01-12 08:00:00',
        'completed_at' => '2026-01-12 08:05:00',
    ];
    Cache::put("unsubscribe_automation_100", $cachedStatus, now()->addMinutes(5));

    // Mock API response for uncached emails (200, 300)
    $apiResponse = [
        'jobs' => [
            [
                'item_id' => 200,
                'status' => 'running',
                'error_message' => null,
                'started_at' => '2026-01-12 09:00:00',
                'completed_at' => null,
            ],
            [
                'item_id' => 300,
                'status' => 'failed',
                'error_message' => 'Network error',
                'started_at' => '2026-01-12 09:30:00',
                'completed_at' => '2026-01-12 09:31:00',
            ],
        ],
    ];

    Http::fake([
        'https://api.test.com/jobs' => Http::response($apiResponse, 200),
    ]);

    // Act
    $service = new UnsubscribeAutomationService();
    $results = $service->getBatchAutomationStatus($emailIds);

    // Assert
    expect($results)->toBeArray()
        ->and($results)->toHaveCount(3);
    
    // Email 100 should be from cache
    expect($results[100])->toBe($cachedStatus);
    
    // Email 200 should be from API (running normalized to processing)
    expect($results[200]['status'])->toBe('processing')
        ->and($results[200]['attempted_at'])->toBe('2026-01-12 09:00:00');
    
    // Email 300 should be from API
    expect($results[300]['status'])->toBe('failed')
        ->and($results[300]['message'])->toBe('Network error');

    // Verify HTTP was called once (batch request)
    Http::assertSentCount(1);
});

it('handles empty email ID array in batch request', function () {
    // Arrange
    Http::fake();

    // Act
    $service = new UnsubscribeAutomationService();
    $results = $service->getBatchAutomationStatus([]);

    // Assert
    expect($results)->toBeArray()
        ->and($results)->toBeEmpty();
    
    Http::assertNothingSent();
});

it('normalizes API status values correctly', function () {
    // Arrange
    Cache::flush();
    $emailIds = [1, 2, 3, 4];

    $apiResponse = [
        'jobs' => [
            ['item_id' => 1, 'status' => 'pending'],      // stays pending
            ['item_id' => 2, 'status' => 'running'],      // becomes processing
            ['item_id' => 3, 'status' => 'completed'],    // stays completed
            ['item_id' => 4, 'status' => 'failed'],       // stays failed
        ],
    ];

    Http::fake([
        'https://api.test.com/jobs' => Http::response($apiResponse, 200),
    ]);

    // Act
    $service = new UnsubscribeAutomationService();
    $results = $service->getBatchAutomationStatus($emailIds);

    // Assert
    expect($results[1]['status'])->toBe('pending')
        ->and($results[2]['status'])->toBe('processing')
        ->and($results[3]['status'])->toBe('completed')
        ->and($results[4]['status'])->toBe('failed');
});

it('clears cache for specific email', function () {
    // Arrange
    $emailId = 777;
    Cache::put("unsubscribe_automation_{$emailId}", ['status' => 'completed'], now()->addMinutes(5));
    
    expect(Cache::has("unsubscribe_automation_{$emailId}"))->toBeTrue();

    // Act
    $service = new UnsubscribeAutomationService();
    $service->clearCache($emailId);

    // Assert
    expect(Cache::has("unsubscribe_automation_{$emailId}"))->toBeFalse();
});

it('handles unconfigured API gracefully', function () {
    // Arrange
    Config::set('services.unsubscribe_api.url', null);
    Cache::flush();

    // Act
    $service = new UnsubscribeAutomationService();
    $result = $service->getAutomationStatus(123);

    // Assert
    expect($result)->toBeArray()
        ->and($result['status'])->toBe('unknown')
        ->and($result['message'])->toBe('API not configured');
});
