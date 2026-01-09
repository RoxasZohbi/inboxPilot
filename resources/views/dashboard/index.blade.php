@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<!-- Welcome Section -->
<div class="mb-8 flex items-center justify-between">
    <div>
        <h1 class="text-4xl font-bold text-white mb-3">
            Welcome back, <span class="bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">{{ Auth::user()->name }}</span>!
        </h1>
        <p class="text-gray-400 text-lg">Manage your inbox smarter with InboxPilot</p>
    </div>
    <div class="flex items-center gap-3">
        <div class="relative group">
            <button class="w-10 h-10 rounded-full bg-gray-800 border border-gray-700 flex items-center justify-center text-gray-400 hover:text-white hover:border-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
            <!-- Tooltip -->
            <div class="absolute right-0 top-12 w-64 bg-gray-800 border border-gray-700 rounded-lg p-3 shadow-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-10">
                <p class="text-sm text-gray-300">Start syncing your emails from your connected Google account</p>
            </div>
        </div>
        <button class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Start Sync
        </button>
    </div>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Stat Card 1 -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-gray-400 text-sm font-medium">Total Emails</p>
                <p class="text-2xl font-bold text-white">0</p>
            </div>
        </div>
    </div>

    <!-- Stat Card 2 -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-gray-400 text-sm font-medium">Automated</p>
                <p class="text-2xl font-bold text-white">0</p>
            </div>
        </div>
    </div>

    <!-- Stat Card 3 -->
    <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-gray-400 text-sm font-medium">Time Saved</p>
                <p class="text-2xl font-bold text-white">0h</p>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<!-- <div class="bg-gray-900 border border-gray-800 rounded-xl p-8 shadow-xl mb-8">
    <h2 class="text-2xl font-bold text-white mb-6">Quick Actions</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <button class="flex items-center gap-4 p-4 bg-gray-800 hover:bg-gray-700 rounded-lg border border-gray-700 transition-colors group">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <div class="text-left">
                <p class="font-semibold text-white group-hover:text-blue-400 transition-colors">Connect Email</p>
                <p class="text-sm text-gray-400">Link your email account</p>
            </div>
        </button>

        <button class="flex items-center gap-4 p-4 bg-gray-800 hover:bg-gray-700 rounded-lg border border-gray-700 transition-colors group">
            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="text-left">
                <p class="font-semibold text-white group-hover:text-purple-400 transition-colors">Setup Automation</p>
                <p class="text-sm text-gray-400">Create email rules</p>
            </div>
        </button>
    </div>
</div> -->

<!-- Getting Started -->
<div class="bg-gray-900 border border-gray-800 rounded-xl p-8 shadow-xl">
    <h2 class="text-2xl font-bold text-white mb-6">Getting Started</h2>
    <div class="space-y-4">
        <div class="flex items-start gap-4">
            <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                <span class="text-white font-bold text-sm">1</span>
            </div>
            <div>
                <h3 class="text-white font-semibold mb-1">Connect your email account</h3>
                <p class="text-gray-400 text-sm">Link your Gmail, Outlook, or other email service to get started</p>
            </div>
        </div>
        <div class="flex items-start gap-4">
            <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                <span class="text-white font-bold text-sm">2</span>
            </div>
            <div>
                <h3 class="text-white font-semibold mb-1">Set up automation rules</h3>
                <p class="text-gray-400 text-sm">Define how InboxPilot should handle your emails automatically</p>
            </div>
        </div>
        <div class="flex items-start gap-4">
            <div class="w-8 h-8 bg-gradient-to-br from-green-500 to-teal-600 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                <span class="text-white font-bold text-sm">3</span>
            </div>
            <div>
                <h3 class="text-white font-semibold mb-1">Enjoy a cleaner inbox</h3>
                <p class="text-gray-400 text-sm">Let AI manage your emails while you focus on what matters</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        console.log('Dashboard loaded successfully');
        
        document.addEventListener('DOMContentLoaded', function() {
            // Your DOM manipulation code
        });
    </script>
@endpush