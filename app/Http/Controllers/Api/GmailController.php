<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GmailController extends Controller
{
    protected GmailService $gmailService;
    
    public function __construct(GmailService $gmailService)
    {
        $this->gmailService = $gmailService;
    }
    
    /**
     * Fetch emails from user's Gmail account
     */
    public function fetchEmails(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            // Check if user has connected their Gmail account
            if (!$user->google_token) {
                return response()->json([
                    'message' => 'Gmail account not connected. Please sign in with Google first.',
                ], 400);
            }
            
            // Set access token for the authenticated user
            $this->gmailService->setAccessToken($user);
            
            // Get max results from request (default: 50, max: 500)
            $maxResults = $request->input('max_results', 50);
            $maxResults = min($maxResults, 500);
            
            // Fetch emails
            $emails = $this->gmailService->fetchEmails($maxResults);
            
            return response()->json([
                'message' => 'Emails fetched successfully',
                'data' => [
                    'emails' => $emails,
                    'count' => count($emails),
                ],
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch emails',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
