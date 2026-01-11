@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<!-- Welcome Section -->
<div class="mb-8">
    <h1 class="text-4xl font-bold text-white mb-3">
        Welcome back, <span class="bg-gradient-to-r from-blue-400 to-purple-500 bg-clip-text text-transparent">{{ Auth::user()->name }}</span>!
    </h1>
    <p class="text-gray-400 text-lg">Manage your inbox smarter with InboxPilot</p>
</div>

<!-- Main Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Section 1: Connected Gmail Accounts -->
    <div class="lg:col-span-1">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-xl h-full">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Gmail Accounts
                </h2>
                @if(Auth::user()->google_token)
                    <span class="text-xs text-gray-400 bg-gray-800 px-2 py-1 rounded">1 connected</span>
                @else
                    <span class="text-xs text-gray-400 bg-gray-800 px-2 py-1 rounded">0 connected</span>
                @endif
            </div>

            <!-- Connected Accounts List -->
            <div class="space-y-3 mb-6">
                @if(Auth::user()->google_token)
                    <!-- Connected Account -->
                    <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 hover:border-gray-600 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    {{ strtoupper(substr(Auth::user()->email, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-white font-medium text-sm">{{ Auth::user()->email }}</p>
                                    <p class="text-gray-400 text-xs">Primary account</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 bg-green-500 rounded-full" title="Connected"></span>
                                <button class="text-gray-400 hover:text-white transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs mt-3 pt-3 border-t border-gray-700">
                            <span class="text-gray-400">{{ Auth::user()->totalEmailsCount() }} emails</span>
                            <span class="text-gray-400">Synced {{ Auth::user()->last_synced_at ? Auth::user()->last_synced_at->diffForHumans() : 'never' }}</span>
                        </div>
                    </div>

                    <!-- Placeholder for more accounts -->
                    <div class="bg-gray-800 border border-gray-700 border-dashed rounded-lg p-4 text-center opacity-50">
                        <p class="text-gray-500 text-sm">Coming soon: Multiple accounts</p>
                    </div>
                @else
                    <!-- No Account Connected -->
                    <div class="bg-gray-800 border-2 border-gray-700 border-dashed rounded-lg p-6 text-center">
                        <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-3">
                            <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-gray-400 text-sm mb-1">No Gmail account connected</p>
                        <p class="text-gray-500 text-xs">Connect your Gmail to get started</p>
                    </div>
                @endif
            </div>

            <!-- Add Account Button -->
            <a href="{{ route('auth.google') }}" class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Connect Gmail Account
            </a>
        </div>
    </div>

    <!-- Section 2 & 3: Custom Categories -->
    <div class="lg:col-span-2">
        <div class="bg-gray-900 border border-gray-800 rounded-xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                    Custom Categories
                    @if($categories->count() > 0)
                        <span class="text-xs text-gray-400 bg-gray-800 px-2 py-1 rounded">{{ $categories->count() }}</span>
                    @endif
                </h2>
                <a href="{{ route('categories.create') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-all duration-300 flex items-center gap-2 shadow-md hover:shadow-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Category
                </a>
            </div>

            <!-- Categories Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @forelse($categories as $category)
                    <x-category-card :category="$category" />
                @empty
                    <!-- Empty State -->
                    <div class="md:col-span-2 bg-gray-800 border-2 border-gray-700 border-dashed rounded-lg p-12 text-center">
                        <div class="w-20 h-20 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <h3 class="text-white font-semibold text-lg mb-2">No categories yet</h3>
                        <p class="text-gray-400 mb-6 max-w-md mx-auto">Create custom categories to organize your emails automatically using AI-powered categorization.</p>
                        <a href="{{ route('categories.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-all duration-300 shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Your First Category
                        </a>
                    </div>
                @endforelse

                @if($categories->count() > 0)
                    <!-- Add More Card -->
                    <a href="{{ route('categories.create') }}" class="bg-gray-800 border-2 border-gray-700 border-dashed rounded-lg p-5 hover:border-gray-600 transition-all duration-300 flex flex-col items-center justify-center text-center cursor-pointer group">
                        <div class="w-12 h-12 bg-gray-700 group-hover:bg-gray-600 rounded-lg flex items-center justify-center mb-3 transition-colors">
                            <svg class="w-6 h-6 text-gray-400 group-hover:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        <h3 class="text-gray-400 group-hover:text-gray-300 font-medium mb-1">Add New Category</h3>
                        <p class="text-gray-500 text-xs">Create a custom email category</p>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Your existing JavaScript remains the same
            // Add any additional functionality for the new UI elements here
        });
    </script>
@endpush
