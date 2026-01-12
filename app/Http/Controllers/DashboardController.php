<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    function index() {
        $user = Auth::user();
        
        // Fetch user's categories with email counts
        $categories = $user->categories()
            ->withCount('emails')
            ->orderBy('priority', 'desc')
            ->orderBy('name')
            ->get();
        
        return view('dashboard.index', compact('categories'));
    }
}
