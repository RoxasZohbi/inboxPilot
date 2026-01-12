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
                <span class="text-xs text-gray-400 bg-gray-800 px-2 py-1 rounded">{{ $googleAccounts->count() }} connected</span>
            </div>

            <!-- Connected Accounts List -->
            <div class="space-y-3 mb-6">
                @forelse($googleAccounts as $account)
                    <!-- Connected Account -->
                    <div class="bg-gray-800 border border-gray-700 rounded-lg p-4 hover:border-gray-600 transition-colors">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                @if($account->avatar)
                                    <img src="{{ $account->avatar }}" alt="{{ $account->name }}" class="w-10 h-10 rounded-full">
                                @else
                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                        {{ strtoupper(substr($account->email, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="text-white font-medium text-sm">{{ $account->email }}</p>
                                    @if($account->is_primary)
                                        <p class="text-gray-400 text-xs">Primary account</p>
                                    @else
                                        <p class="text-gray-400 text-xs">{{ $account->name }}</p>
                                    @endif
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
                            <span class="text-gray-400">{{ $account->emails_count ?? 0 }} emails</span>
                            <span class="text-gray-400">Synced {{ $account->last_synced_at ? $account->last_synced_at->diffForHumans() : 'never' }}</span>
                        </div>
                        
                        <!-- Sync Button -->
                        <div class="mt-3">
                            <button class="sync-btn w-full px-3 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-700 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-lg transition-all duration-200 flex items-center justify-center gap-2"
                                    data-account-id="{{ $account->id }}"
                                    data-account-email="{{ $account->email }}">
                                <svg class="w-4 h-4 sync-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                <span class="sync-text">Sync Emails</span>
                            </button>
                        </div>
                    </div>
                @empty
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
                @endforelse
            </div>

            <!-- Add Account Button -->
            <a href="{{ route('auth.google') }}" class="w-full px-4 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                @if($googleAccounts->count() > 0)
                    Add Another Account
                @else
                    Connect Gmail Account
                @endif
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
                <div class="flex items-center gap-3">
                    <button class="process-all-btn px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:from-gray-700 disabled:to-gray-700 disabled:cursor-not-allowed text-white font-semibold rounded-lg transition-all duration-300 flex items-center gap-2 shadow-md hover:shadow-lg"
                            data-pending-count="{{ $totalPendingCount ?? 0 }}"
                            @if(($totalPendingCount ?? 0) === 0) disabled @endif>
                        <svg class="w-5 h-5 process-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <span class="process-text">Process All AI ({{ $totalPendingCount ?? 0 }})</span>
                    </button>
                    <a href="{{ route('categories.create') }}" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-all duration-300 flex items-center gap-2 shadow-md hover:shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Category
                    </a>
                </div>
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
            // CSRF Token setup for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                }
            });

            // Sync Emails Button Handler
            $('.sync-btn').on('click', function() {
                const $btn = $(this);
                const accountId = $btn.data('account-id');
                const accountEmail = $btn.data('account-email');
                
                // Disable button and show loading state
                $btn.prop('disabled', true);
                $btn.find('.sync-icon').addClass('animate-spin');
                $btn.find('.sync-text').text('Syncing...');
                
                // Make API request
                $.ajax({
                    url: '/api/gmail/sync',
                    method: 'POST',
                    data: {
                        account_id: accountId,
                        max_results: 100
                    },
                    success: function(response) {
                        // Show success notification
                        showNotification('success', response.message || 'Email sync started successfully!');
                        
                        // Re-enable button after 5 seconds
                        setTimeout(() => {
                            $btn.prop('disabled', false);
                            $btn.find('.sync-icon').removeClass('animate-spin');
                            $btn.find('.sync-text').text('Sync Emails');
                        }, 5000);
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Failed to start email sync';
                        showNotification('error', errorMsg);
                        
                        // Re-enable button
                        $btn.prop('disabled', false);
                        $btn.find('.sync-icon').removeClass('animate-spin');
                        $btn.find('.sync-text').text('Sync Emails');
                    }
                });
            });

            // Process All Pending Emails Button Handler
            $('.process-all-btn').on('click', function() {
                const $btn = $(this);
                const pendingCount = $btn.data('pending-count');
                
                if (pendingCount === 0) return;
                
                // Disable button and show loading state
                $btn.prop('disabled', true);
                $btn.find('.process-icon').addClass('animate-spin');
                $btn.find('.process-text').text('Processing...');
                
                // Make API request (no account_id means process all)
                $.ajax({
                    url: '/api/emails/process-pending',
                    method: 'POST',
                    data: {},
                    success: function(response) {
                        // Show success notification
                        showNotification('success', response.message || 'AI processing jobs queued successfully!');
                        
                        // Reload page after 3 seconds to update counts
                        setTimeout(() => {
                            location.reload();
                        }, 3000);
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Failed to process pending emails';
                        showNotification('error', errorMsg);
                        
                        // Re-enable button
                        $btn.prop('disabled', false);
                        $btn.find('.process-icon').removeClass('animate-spin');
                        $btn.find('.process-text').text(`Process All AI (${pendingCount})`);
                    }
                });
            });

            // Notification Helper Function
            function showNotification(type, message) {
                // Create notification element
                const bgColor = type === 'success' ? 'bg-green-600' : 'bg-red-600';
                const icon = type === 'success' 
                    ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                    : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                
                const $notification = $(`
                    <div class="fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-2xl flex items-center gap-3 z-50 animate-slide-in">
                        ${icon}
                        <span class="font-medium">${message}</span>
                    </div>
                `);
                
                // Append to body
                $('body').append($notification);
                
                // Remove after 5 seconds
                setTimeout(() => {
                    $notification.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 5000);
            }

            // Add CSS for animation
            if (!$('#notification-styles').length) {
                $('head').append(`
                    <style id="notification-styles">
                        @keyframes slide-in {
                            from {
                                transform: translateX(100%);
                                opacity: 0;
                            }
                            to {
                                transform: translateX(0);
                                opacity: 1;
                            }
                        }
                        .animate-slide-in {
                            animation: slide-in 0.3s ease-out;
                        }
                    </style>
                `);
            }
        });
    </script>
@endpush
