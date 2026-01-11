<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Email;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailController extends Controller
{
    /**
     * Get user's emails with pagination and filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $query = $user->emails()->with('category')->latest('date');

            // Filter by unread
            if ($request->boolean('unread')) {
                $query->unread();
            }

            // Filter by starred
            if ($request->boolean('starred')) {
                $query->starred();
            }

            // Filter by has_attachments
            if ($request->boolean('has_attachments')) {
                $query->withAttachments();
            }

            // Filter by date range
            if ($request->has('date_from')) {
                $query->where('date', '>=', $request->date('date_from'));
            }
            if ($request->has('date_to')) {
                $query->where('date', '<=', $request->date('date_to'));
            }

            // Filter by category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->input('category_id'));
            }

            // Search by subject or sender
            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('subject', 'like', "%{$search}%")
                      ->orWhere('from_email', 'like', "%{$search}%")
                      ->orWhere('from_name', 'like', "%{$search}%");
                });
            }

            // Paginate results
            $perPage = min($request->input('per_page', 20), 100);
            $emails = $query->paginate($perPage);

            return response()->json([
                'message' => 'Emails retrieved successfully',
                'data' => $emails,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve emails',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a single email by ID
     */
    public function show(Email $email): JsonResponse
    {
        try {
            // Ensure email belongs to authenticated user
            if ($email->user_id !== Auth::id()) {
                return response()->json([
                    'message' => 'Email not found',
                ], 404);
            }
            
            $email->load('category');

            return response()->json([
                'id' => $email->id,
                'subject' => $email->subject,
                'from_name' => $email->from_name,
                'from_email' => $email->from_email,
                'to' => $email->to,
                'date' => $email->date?->toIso8601String(),
                'body' => text_to_html($email->body ?? ''),
                'snippet' => $email->snippet,
                'ai_summary' => $email->ai_summary,
                'is_starred' => (bool) $email->is_starred,
                'is_unread' => (bool) $email->is_unread,
                'has_attachments' => (bool) $email->has_attachments,
                'category' => $email->category ? [
                    'id' => $email->category->id,
                    'name' => $email->category->name,
                    'priority' => $email->category->priority,
                ] : null,
                'created_at' => $email->created_at?->toIso8601String(),
                'updated_at' => $email->updated_at?->toIso8601String(),
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve email',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Mark email as read/unread
     */
    public function toggleRead(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $email = $user->emails()->findOrFail($id);
            $email->update(['is_unread' => !$email->is_unread]);

            return response()->json([
                'message' => 'Email status updated',
                'data' => $email,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update email',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle email starred status
     */
    public function toggleStar(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $email = $user->emails()->findOrFail($id);
            $email->update(['is_starred' => !$email->is_starred]);

            return response()->json([
                'message' => 'Email starred status updated',
                'data' => $email,
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update email',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete (soft delete) an email
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = Auth::user();
            
            $email = $user->emails()->findOrFail($id);
            $email->delete();

            return response()->json([
                'message' => 'Email deleted successfully',
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete email',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
