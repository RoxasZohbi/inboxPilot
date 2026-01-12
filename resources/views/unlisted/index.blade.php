@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white">Unlisted Emails</h1>
            <p class="text-gray-400 mt-2">Emails pending AI categorization</p>
        </div>
        @if($emails->total() > 0)
            <button id="process-all-btn" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg font-medium hover:from-blue-600 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Process All ({{ $emails->total() }})</span>
                </div>
            </button>
        @endif
    </div>

    <!-- Stats Card -->
    <div class="bg-gradient-to-r from-blue-500/10 to-purple-600/10 border border-blue-500/20 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-400 text-sm">Total Unlisted Pending Emails</p>
                <p class="text-3xl font-bold text-white mt-1">{{ $emails->total() }}</p>
            </div>
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>

    <!-- Emails Table -->
    <div class="bg-gray-800 rounded-xl shadow-xl overflow-hidden border border-gray-700">
        @if($emails->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-900 border-b border-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">
                                Sender
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">
                                Subject
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-300 uppercase tracking-wider">
                                Account
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($emails as $email)
                            <tr class="hover:bg-gray-750 transition-colors cursor-pointer" onclick="showEmailDetails({{ $email->id }})">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold">
                                            {{ strtoupper(substr($email->from_name ?: $email->from_email, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-white font-medium">{{ $email->from_name ?: $email->from_email }}</div>
                                            <div class="text-gray-400 text-sm">{{ $email->from_email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-white font-medium">{{ $email->subject ?: '(No Subject)' }}</div>
                                    @if($email->snippet)
                                        <div class="text-gray-400 text-sm mt-1">{{ Str::limit($email->snippet, 80) }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-300 text-sm">
                                        {{ $email->date->format('M d, Y') }}
                                    </div>
                                    <div class="text-gray-500 text-xs">
                                        {{ $email->date->format('h:i A') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusConfig = [
                                            'pending' => ['bg' => 'bg-yellow-500/10', 'text' => 'text-yellow-400', 'border' => 'border-yellow-500/20', 'label' => 'Pending'],
                                            'processing' => ['bg' => 'bg-blue-500/10', 'text' => 'text-blue-400', 'border' => 'border-blue-500/20', 'label' => 'Processing'],
                                            'completed' => ['bg' => 'bg-green-500/10', 'text' => 'text-green-400', 'border' => 'border-green-500/20', 'label' => 'Completed'],
                                            'failed' => ['bg' => 'bg-red-500/10', 'text' => 'text-red-400', 'border' => 'border-red-500/20', 'label' => 'Failed'],
                                        ];
                                        $status = $email->status ?? 'pending';
                                        $config = $statusConfig[$status] ?? $statusConfig['pending'];
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border {{ $config['bg'] }} {{ $config['text'] }} {{ $config['border'] }}">
                                        {{ $config['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-gray-300 text-sm">
                                        {{ $email->googleAccount->email }}
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-gray-900 px-6 py-4 border-t border-gray-700">
                {{ $emails->links() }}
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-20 h-20 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-white mb-2">All Caught Up!</h3>
                <p class="text-gray-400">No unlisted pending emails at the moment.</p>
            </div>
        @endif
    </div>
</div>

<!-- Email Details Modal (reuse from existing component if available) -->
<div id="email-modal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4">
    <div class="bg-gray-800 rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden border border-gray-700">
        <!-- Modal content will be loaded via JavaScript -->
        <div id="modal-content"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Process All button handler
    $('#process-all-btn').click(function() {
        const btn = $(this);
        const originalHtml = btn.html();
        
        if (confirm('Are you sure you want to process all unlisted pending emails with AI? This may take some time.')) {
            // Disable button and show loading state
            btn.prop('disabled', true);
            btn.html(`
                <div class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Processing...</span>
                </div>
            `);
            
            $.ajax({
                url: '/api/unlisted-emails/process-all',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    alert(response.message);
                    // Reload the page to show updated status
                    location.reload();
                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON?.message || 'An error occurred while processing emails.';
                    alert(errorMessage);
                    btn.prop('disabled', false);
                    btn.html(originalHtml);
                }
            });
        }
    });
    
    // Close modal handler
    $(document).on('click', '#close-modal', function() {
        $('#email-modal').addClass('hidden');
    });
    
    // Close modal when clicking outside
    $('#email-modal').click(function(e) {
        if (e.target === this) {
            $(this).addClass('hidden');
        }
    });
});

// Email details modal function (placeholder - implement based on existing pattern)
function showEmailDetails(emailId) {
    // Implement email details modal similar to existing implementation
    console.log('Show details for email:', emailId);
    // You can fetch email details via API and display in modal
}
</script>
@endpush
