<?php

namespace App\Http\Controllers;

use App\Jobs\SyncGmailEmailsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    function index() {
        $user = Auth::user();
        
        // Auto-start sync if:
        // 1. User has Google token (Gmail connected)
        // 2. No sync is currently running
        // 3. Last synced > 5 minutes ago OR never synced
        if ($user->google_token && 
            !Cache::has("gmail_sync:{$user->id}") &&
            (!$user->last_synced_at || $user->last_synced_at->diffInMinutes(now()) >= 5)) {
            
            $syncLimit = config('app.gmail_sync_limit', 500);
            SyncGmailEmailsJob::dispatch($user, $syncLimit);
        }
        
        return view('dashboard.index');
    }
}
