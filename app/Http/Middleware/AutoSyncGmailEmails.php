<?php

namespace App\Http\Middleware;

use App\Jobs\SyncGmailEmailsJob;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class AutoSyncGmailEmails
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only run for authenticated users
        if (Auth::check()) {
            $user = Auth::user();
            
            // Get all Google accounts for the user
            $googleAccounts = $user->googleAccounts;
            
            if ($googleAccounts->isNotEmpty()) {
                $syncLimit = config('app.gmail_sync_limit', 500);
                
                // Auto-start sync for each Google account if:
                // 1. No sync is currently running for this account
                // 2. Last synced > 2 minutes ago OR never synced
                foreach ($googleAccounts as $account) {
                    $cacheKey = "gmail_sync:{$user->id}:{$account->id}";
                    
                    Log::info("Checking auto-sync conditions for Google account {$account->id}", [
                        'email' => $account->email,
                        'is_sync_running' => Cache::has($cacheKey),
                        'last_synced_at' => $account->last_synced_at ? $account->last_synced_at->toIso8601String() : null,
                        'minutes_since_last_sync' => $account->last_synced_at ? $account->last_synced_at->diffInMinutes(now()) : null,
                    ]);
                    
                    if (!Cache::has($cacheKey) &&
                        (!$account->last_synced_at || $account->last_synced_at->diffInMinutes(now()) >= 2)) {
                        
                        SyncGmailEmailsJob::dispatch($account, $syncLimit);
                    }
                }
            }
        }
        
        return $next($request);
    }
}
