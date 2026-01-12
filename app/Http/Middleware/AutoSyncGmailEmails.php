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
            
            // Auto-start sync if:
            // 1. User has Google token (Gmail connected)
            // 2. No sync is currently running
            // 3. Last synced > 2 minutes ago OR never synced
            Log::info('Checking auto-sync conditions for user ID: '.$user->id. '.'. json_encode([
                'has_google_token' => (bool)$user->google_token,
                'is_sync_running' => Cache::has("gmail_sync:{$user->id}"),
                'last_synced_at' => $user->last_synced_at ? $user->last_synced_at->toIso8601String() : null,
                'minutes_since_last_sync' => $user->last_synced_at ? $user->last_synced_at->diffInMinutes(now()) : null,
            ]));
            if ($user->google_token && 
                !Cache::has("gmail_sync:{$user->id}") &&
                (!$user->last_synced_at || $user->last_synced_at->diffInMinutes(now()) >= 2)) {
                
                $syncLimit = config('app.gmail_sync_limit', 500);
                SyncGmailEmailsJob::dispatch($user, $syncLimit);
            }
        }
        
        return $next($request);
    }
}
