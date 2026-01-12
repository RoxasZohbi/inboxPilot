<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnsubscribeEmailController extends Controller
{
    /**
     * Display a listing of emails with unsubscribe available.
     */
    public function index()
    {
        $emails = Auth::user()->emails()
            ->withUnsubscribe()
            ->with(['googleAccount', 'category'])
            ->orderBy('date', 'desc')
            ->paginate(10);

        return view('unsubscribe.index', compact('emails'));
    }

    /**
     * Handle manual unsubscribe (opens URL in new tab - frontend only).
     */
    public function manualUnsubscribe(Request $request)
    {
        // This will be handled on frontend by opening unsubscribe_url in new tab
        return response()->json([
            'message' => 'Manual unsubscribe initiated'
        ]);
    }

    /**
     * Handle automated unsubscribe (design only - not yet implemented).
     */
    public function automatedUnsubscribe(Request $request)
    {
        // TODO: Implement automated unsubscribe logic
        return response()->json([
            'message' => 'Automated unsubscribe feature coming soon'
        ]);
    }

    /**
     * Handle bulk automated unsubscribe (design only - not yet implemented).
     */
    public function bulkUnsubscribe(Request $request)
    {
        $request->validate([
            'email_ids' => 'required|array',
            'email_ids.*' => 'exists:emails,id'
        ]);

        // TODO: Implement bulk automated unsubscribe logic
        // Verify that all emails belong to authenticated user
        
        return response()->json([
            'message' => 'Bulk automated unsubscribe feature coming soon'
        ]);
    }
}
