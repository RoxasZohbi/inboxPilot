<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    function index() {
        $user = Auth::user();
        
        // Fetch user's Google accounts with email counts and pending counts
        $googleAccounts = $user->googleAccounts()
            ->withCount('emails')
            ->withCount([
                'emails as pending_emails_count' => function ($query) {
                    $query->where(function ($q) {
                        $q->whereNull('status')
                          ->orWhere('status', 'pending');
                    })->whereNull('processed_at');
                }
            ])
            ->orderBy('is_primary', 'desc')
            ->orderBy('email')
            ->get();
        
        // Fetch user's categories with email counts
        $categories = $user->categories()
            ->withCount('emails')
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get();
        
        // Get total pending emails count across all accounts
        $googleAccountIds = $googleAccounts->pluck('id');
        $totalPendingCount = \App\Models\Email::whereIn('google_account_id', $googleAccountIds)
            ->where(function ($query) {
                $query->whereNull('status')
                      ->orWhere('status', 'pending');
            })
            ->whereNull('processed_at')
            ->count();
        
        return view('dashboard.index', compact('categories', 'googleAccounts', 'totalPendingCount'));
    }
}
