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
            $accountId = $request->input('account_id');
            
            // If account_id is provided, sync specific account, otherwise sync all accounts
            if ($accountId) {
                // Get specific Google account
                $googleAccount = $user->googleAccounts()->find($accountId);
                
                if (!$googleAccount) {
                    return response()->json([
                        'message' => 'Google account not found or does not belong to you.',
                    ], 404);
                }
                
                // Check if sync is already in progress for this account
                $cacheKey = "gmail_sync:{$user->id}:{$googleAccount->id}";
                $syncStatus = Cache::get($cacheKey);
                
                if ($syncStatus && $syncStatus['status'] === 'processing') {
                    return response()->json([
                        'message' => 'Sync already in progress for this account',
                        'data' => $syncStatus,
                    ], 409);
                }
                
                // Get max results from request (default: 100, max: 500)
                $maxResults = $request->input('max_results', 100);
                $maxResults = min($maxResults, 500);
                
                // Dispatch sync job for this account
                SyncGmailEmailsJob::dispatch($googleAccount, $maxResults);
                
                return response()->json([
                    'message' => 'Email sync started successfully for account: ' . $googleAccount->email,
                    'data' => [
                        'status' => 'pending',
                        'account_id' => $googleAccount->id,
                        'account_email' => $googleAccount->email,
                        'max_results' => $maxResults,
                    ],
                ], 202);
                
            } else {
                // Sync all Google accounts
                $googleAccounts = $user->googleAccounts;
                
                if ($googleAccounts->isEmpty()) {
                    return response()->json([
                        'message' => 'No Gmail accounts connected. Please sign in with Google first.',
                    ], 400);
                }
                
                // Get max results from request (default: 100, max: 500)
                $maxResults = $request->input('max_results', 100);
                $maxResults = min($maxResults, 500);
                
                $dispatched = [];
                $alreadyRunning = [];
                
                foreach ($googleAccounts as $account) {
                    $cacheKey = "gmail_sync:{$user->id}:{$account->id}";
                    $syncStatus = Cache::get($cacheKey);
                    
                    if ($syncStatus && $syncStatus['status'] === 'processing') {
                        $alreadyRunning[] = $account->email;
                    } else {
                        SyncGmailEmailsJob::dispatch($account, $maxResults);
                        $dispatched[] = $account->email;
                    }
                }
                
                return response()->json([
                    'message' => 'Email sync started for ' . count($dispatched) . ' account(s)',
                    'data' => [
                        'status' => 'pending',
                        'dispatched' => $dispatched,
                        'already_running' => $alreadyRunning,
                        'max_results' => $maxResults,
                    ],
                ], 202);
            }
            
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
    public function syncStatus(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $accountId = $request->input('account_id');
            
            if ($accountId) {
                // Get status for specific account
                $googleAccount = $user->googleAccounts()->find($accountId);
                
                if (!$googleAccount) {
                    return response()->json([
                        'message' => 'Google account not found or does not belong to you.',
                    ], 404);
                }
                
                $cacheKey = "gmail_sync:{$user->id}:{$googleAccount->id}";
                $syncStatus = Cache::get($cacheKey);
                
                if (!$syncStatus) {
                    return response()->json([
                        'message' => 'No sync in progress for this account',
                        'data' => [
                            'status' => 'idle',
                            'account_id' => $googleAccount->id,
                            'account_email' => $googleAccount->email,
                            'last_synced_at' => $googleAccount->last_synced_at?->toIso8601String(),
                        ],
                    ], 200);
                }
                
                return response()->json([
                    'message' => 'Sync status retrieved',
                    'data' => array_merge($syncStatus, [
                        'account_id' => $googleAccount->id,
                        'account_email' => $googleAccount->email,
                    ]),
                ], 200);
                
            } else {
                // Get status for all accounts
                $googleAccounts = $user->googleAccounts;
                
                if ($googleAccounts->isEmpty()) {
                    return response()->json([
                        'message' => 'No Gmail accounts connected',
                        'data' => [
                            'status' => 'idle',
                            'accounts' => [],
                        ],
                    ], 200);
                }
                
                $accountsStatus = [];
                foreach ($googleAccounts as $account) {
                    $cacheKey = "gmail_sync:{$user->id}:{$account->id}";
                    $syncStatus = Cache::get($cacheKey);
                    
                    $accountsStatus[] = [
                        'account_id' => $account->id,
                        'account_email' => $account->email,
                        'status' => $syncStatus ? $syncStatus['status'] : 'idle',
                        'sync_data' => $syncStatus ?? null,
                        'last_synced_at' => $account->last_synced_at?->toIso8601String(),
                    ];
                }
                
                return response()->json([
                    'message' => 'Sync status retrieved for all accounts',
                    'data' => [
                        'accounts' => $accountsStatus,
                    ],
                ], 200);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get sync status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
