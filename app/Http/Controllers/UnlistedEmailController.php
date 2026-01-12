<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Jobs\ProcessEmailWithAIJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnlistedEmailController extends Controller
{
    /**
     * Display a listing of unlisted pending emails.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get all Google account IDs for the authenticated user
        $googleAccountIds = $user->googleAccounts()->pluck('id')->toArray();

        // Get unlisted pending emails
        $emails = Email::whereIn('google_account_id', $googleAccountIds)
            ->unlisted()
            ->pending()
            ->with('googleAccount')
            ->orderBy('date', 'desc')
            ->paginate(20);

        // Get the count for the badge
        $unlistedPendingCount = Email::whereIn('google_account_id', $googleAccountIds)
            ->unlisted()
            ->pending()
            ->count();

        return view('unlisted.index', compact('emails', 'unlistedPendingCount'));
    }

    /**
     * Process all unlisted pending emails with AI.
     */
    public function processAll(Request $request)
    {
        $user = Auth::user();
        
        // Get all Google account IDs for the authenticated user
        $googleAccountIds = $user->googleAccounts()->pluck('id')->toArray();

        // Get all unlisted pending emails
        $emails = Email::whereIn('google_account_id', $googleAccountIds)
            ->unlisted()
            ->pending()
            ->get();

        $count = $emails->count();

        if ($count === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No unlisted pending emails to process.'
            ], 404);
        }

        // Dispatch AI processing jobs for each email
        foreach ($emails as $email) {
            ProcessEmailWithAIJob::dispatch($email);
        }

        return response()->json([
            'success' => true,
            'message' => "{$count} email(s) have been queued for AI processing.",
            'count' => $count
        ]);
    }
}
