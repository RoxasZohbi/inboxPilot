<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SyncGmailEmailsJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class GmailController extends Controller
{
    /**
     * Start syncing emails from user's Gmail account (async with jobs)
     */
    public function syncEmails(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user has connected their Gmail account
            if (!$user->google_token) {
                return response()->json([
                    'message' => 'Gmail account not connected. Please sign in with Google first.',
                ], 400);
            }

            // Check if sync is already in progress
            $cacheKey = "gmail_sync:{$user->id}";
            $syncStatus = Cache::get($cacheKey);
            
            if ($syncStatus && $syncStatus['status'] === 'processing') {
                return response()->json([
                    'message' => 'Sync already in progress',
                    'data' => $syncStatus,
                ], 409);
            }

            // Get max results from request (default: 100, max: 500)
            $maxResults = $request->input('max_results', 100);
            $maxResults = min($maxResults, 500);

            // Dispatch sync job
            SyncGmailEmailsJob::dispatch($user, $maxResults);

            return response()->json([
                'message' => 'Email sync started successfully',
                'data' => [
                    'status' => 'pending',
                    'max_results' => $maxResults,
                ],
            ], 202);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to start email sync',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the current sync status
     */
    public function syncStatus(): JsonResponse
    {
        try {
            $user = Auth::user();
            $cacheKey = "gmail_sync:{$user->id}";
            
            $syncStatus = Cache::get($cacheKey);
            
            if (!$syncStatus) {
                return response()->json([
                    'message' => 'No sync in progress',
                    'data' => [
                        'status' => 'idle',
                        'last_synced_at' => $user->last_synced_at?->toIso8601String(),
                    ],
                ], 200);
            }

            return response()->json([
                'message' => 'Sync status retrieved',
                'data' => $syncStatus,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get sync status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
