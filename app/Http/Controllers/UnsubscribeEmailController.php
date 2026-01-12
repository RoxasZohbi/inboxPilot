<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Services\UnsubscribeAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnsubscribeEmailController extends Controller
{
    protected UnsubscribeAutomationService $automationService;

    public function __construct(UnsubscribeAutomationService $automationService)
    {
        $this->automationService = $automationService;
    }

    /**
     * Display a listing of emails with unsubscribe available.
     * 
     * Fetches automation status from external API for each email.
     * The automation data is not stored in database but merged dynamically:
     * - automation_status: pending, processing, completed, failed, unavailable, unknown
     * - automation_message: Error message or status description
     * - automation_attempted_at: When automation was attempted (ISO 8601 format)
     * - automation_completed_at: When automation completed (ISO 8601 format)
     * 
     * API responses are cached for 5 minutes to improve performance.
     */
    public function index()
    {
        $emails = Auth::user()->emails()
            ->withUnsubscribe()
            ->with(['googleAccount', 'category'])
            ->orderBy('date', 'desc')
            ->paginate(10);

        // Fetch automation status for all emails from external API
        $emailIds = $emails->pluck('id')->toArray();
        $automationStatuses = $this->automationService->getBatchAutomationStatus($emailIds);

        // Merge automation data into each email object
        $emails->getCollection()->transform(function ($email) use ($automationStatuses) {
            $status = $automationStatuses[$email->id] ?? [
                'status' => 'unknown',
                'message' => null,
                'attempted_at' => null,
                'completed_at' => null,
            ];

            // Add automation attributes to email object
            $email->automation_status = $status['status'];
            $email->automation_message = $status['message'];
            $email->automation_attempted_at = $status['attempted_at'];
            $email->automation_completed_at = $status['completed_at'];

            return $email;
        });

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
