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
        <button id="syncGmailBtn" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-lg transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
            <svg id="syncIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span id="syncBtnText">
                @if(Auth::user()->last_synced_at)
                    Last synced: {{ Auth::user()->last_synced_at->diffForHumans() }}
                @else
                    Checking...
                @endif
            </span>
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
                <p id="totalEmailsCount" class="text-2xl font-bold text-white">{{ Auth::user()->totalEmailsCount() }}</p>
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
        $(document).ready(function() {
            let syncPollingInterval = null;
            
            // Auto-check sync status on page load
            checkSyncStatus();
            
            // Gmail Sync Button Handler (Manual Sync)
            $('#syncGmailBtn').on('click', function(e) {
                e.preventDefault();
                
                // Confirm with user for manual sync
                if (!confirm('This will sync your latest emails from Gmail. Continue?')) {
                    return;
                }
                
                const $btn = $(this);
                const $btnText = $('#syncBtnText');
                const $syncIcon = $('#syncIcon');
                
                // Disable button and show loading state
                $btn.prop('disabled', true);
                $btnText.text('Starting...');
                $syncIcon.addClass('animate-spin');
                
                // Make AJAX request to start sync
                $.ajax({
                    url: '/api/gmail/sync',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                    },
                    data: {
                        max_results: 100
                    },
                    success: function(response) {
                        console.log('Sync started:', response);
                        $btnText.text('Syncing...');
                        
                        // Start polling for status
                        startStatusPolling();
                    },
                    error: function(xhr) {
                        $syncIcon.removeClass('animate-spin');
                        $btn.prop('disabled', false);
                        $btnText.text('Sync Now');
                        
                        let errorMessage = 'Failed to start sync. Please try again.';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 401) {
                            errorMessage = 'Authentication required. Please sign in with Google first.';
                        } else if (xhr.status === 400) {
                            errorMessage = 'Gmail account not connected. Please sign in with Google.';
                        } else if (xhr.status === 409) {
                            errorMessage = 'Sync already in progress. Please wait...';
                            startStatusPolling();
                        }
                        
                        alert(errorMessage);
                        console.error('Error:', xhr.responseJSON || xhr.statusText);
                    }
                });
            });
            
            // Poll sync status
            function startStatusPolling() {
                if (syncPollingInterval) {
                    clearInterval(syncPollingInterval);
                }
                
                syncPollingInterval = setInterval(checkSyncStatus, 2000); // Poll every 2 seconds
                checkSyncStatus(); // Check immediately
            }
            
            function checkSyncStatus() {
                $.ajax({
                    url: '/api/gmail/sync-status',
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                        'Accept': 'application/json',
                    },
                    success: function(response) {
                        const status = response.data;
                        console.log('Sync status:', status);
                        
                        if (status.status === 'processing') {
                            updateSyncProgress(status);
                        } else if (status.status === 'completed') {
                            syncCompleted(status);
                        } else if (status.status === 'failed') {
                            syncFailed(status);
                        } else if (status.status === 'idle') {
                            // No sync in progress
                            resetSyncButton();
                        }
                    },
                    error: function(xhr) {
                        console.error('Failed to check status:', xhr);
                    }
                });
            }
            
            function updateSyncProgress(status) {
                const processed = status.processed || 0;
                const total = status.total_emails || 0;
                const failed = status.failed || 0;
                
                let percentage = total > 0 ? Math.round((processed / total) * 100) : 0;
                
                $('#syncBtnText').text(`Syncing... ${processed}/${total}`);
                
                // Update stats in real-time
                if (processed > 0) {
                    updateEmailCount();
                }
            }
            
            function syncCompleted(status) {
                clearInterval(syncPollingInterval);
                syncPollingInterval = null;
                
                const $syncIcon = $('#syncIcon');
                const $btnText = $('#syncBtnText');
                const $btn = $('#syncGmailBtn');
                
                $syncIcon.removeClass('animate-spin');
                $btnText.text('Sync Complete!');
                
                // Update email count
                updateEmailCount();
                
                const processed = status.processed || 0;
                const failed = status.failed || 0;
                
                alert(`Successfully synced ${processed} emails!${failed > 0 ? ` (${failed} failed)` : ''}`);
                
                // Re-enable button and update text
                setTimeout(function() {
                    $btn.prop('disabled', false);
                    $btnText.text('Sync Now');
                }, 3000);
            }
            
            function syncFailed(status) {
                clearInterval(syncPollingInterval);
                syncPollingInterval = null;
                
                const $syncIcon = $('#syncIcon');
                const $btnText = $('#syncBtnText');
                const $btn = $('#syncGmailBtn');
                
                $syncIcon.removeClass('animate-spin');
                $btn.prop('disabled', false);
                $btnText.text('Sync Now');
                
                alert('Sync failed: ' + (status.error || 'Unknown error'));
                console.error('Sync error:', status);
            }
            
            function resetSyncButton() {
                if (syncPollingInterval) {
                    clearInterval(syncPollingInterval);
                    syncPollingInterval = null;
                }
                
                const $syncIcon = $('#syncIcon');
                const $btnText = $('#syncBtnText');
                const $btn = $('#syncGmailBtn');
                
                $syncIcon.removeClass('animate-spin');
                $btn.prop('disabled', false);
                $btnText.text('Sync Now');
            }
            
            function updateEmailCount() {
                // Fetch updated email count
                $.ajax({
                    url: '/api/user',
                    type: 'GET',
                    headers: {
                        'Accept': 'application/json',
                    },
                    success: function(response) {
                        // Refresh the count via page reload or update dynamically
                        // For now, we'll just increment (proper way would be to fetch from API)
                        location.reload();
                    }
                });
            }
        });
    </script>
@endpush