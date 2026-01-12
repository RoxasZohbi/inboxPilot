<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    function index() {
        $user = Auth::user();
        
        // Fetch user's Google accounts with email counts
        $googleAccounts = $user->googleAccounts()
            ->withCount('emails')
            ->orderBy('is_primary', 'desc')
            ->orderBy('email')
            ->get();
        
        // Fetch user's categories with email counts
        $categories = $user->categories()
            ->withCount('emails')
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get();
        
        return view('dashboard.index', compact('categories', 'googleAccounts'));
    }
}
