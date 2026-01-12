@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-white">Unsubscribe Management</h1>
            <p class="text-gray-400 mt-2">Manage emails with unsubscribe options</p>
        </div>
    </div>

    <!-- Bulk Actions Bar (hidden by default) -->
    <div id="bulk-actions-bar" class="hidden bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl p-4 shadow-xl">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="text-white font-medium">
                    <span id="selected-count">0</span> email(s) selected
                </span>
                <button id="deselect-all" class="text-white hover:text-gray-200 underline text-sm">
                    Deselect All
                </button>
            </div>
            <div class="flex items-center gap-3">
                <button id="bulk-unsubscribe-btn" class="px-6 py-2 bg-white text-purple-600 rounded-lg font-medium hover:bg-gray-100 transition-colors shadow-lg">
                    Bulk Automated Unsubscribe
                </button>
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
                            <th class="px-6 py-4 text-left">
                                <input type="checkbox" id="select-all" class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-purple-500 focus:ring-purple-500 focus:ring-offset-gray-800">
                            </th>
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
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach($emails as $email)
                            <tr class="hover:bg-gray-750 transition-colors">
                                <td class="px-6 py-4">
                                    <input type="checkbox" class="email-checkbox w-4 h-4 rounded border-gray-600 bg-gray-700 text-purple-500 focus:ring-purple-500 focus:ring-offset-gray-800" data-email-id="{{ $email->id }}" data-unsubscribe-url="{{ $email->unsubscribe_url }}">
                                </td>
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
                                        <div class="text-gray-400 text-sm mt-1">{{ Str::limit($email->snippet, 60) }}</div>
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
                                    <div class="flex items-center gap-2">
                                        @if($email->unsubscribe_url)
                                            <button onclick="window.open('{{ $email->unsubscribe_url }}', '_blank')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                Unsubscribe Manually
                                            </button>
                                        @else
                                            <span class="px-4 py-2 bg-gray-700 text-gray-400 text-sm font-medium rounded-lg cursor-not-allowed">
                                                No URL Available
                                            </span>
                                        @endif
                                        <button class="automated-unsubscribe-btn px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white text-sm font-medium rounded-lg transition-all shadow-lg" data-email-id="{{ $email->id }}">
                                            Automated Unsubscribe
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($emails->hasPages())
                <div class="px-6 py-4 border-t border-gray-700 bg-gray-900">
                    <div class="flex items-center justify-between">
                        <div class="text-gray-400 text-sm">
                            Showing {{ $emails->firstItem() }} to {{ $emails->lastItem() }} of {{ $emails->total() }} emails
                        </div>
                        <div class="flex items-center gap-2">
                            @if ($emails->onFirstPage())
                                <span class="px-4 py-2 bg-gray-800 text-gray-500 rounded-lg cursor-not-allowed">Previous</span>
                            @else
                                <a href="{{ $emails->previousPageUrl() }}" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">Previous</a>
                            @endif

                            <span class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg font-medium">
                                Page {{ $emails->currentPage() }} of {{ $emails->lastPage() }}
                            </span>

                            @if ($emails->hasMorePages())
                                <a href="{{ $emails->nextPageUrl() }}" class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition-colors">Next</a>
                            @else
                                <span class="px-4 py-2 bg-gray-800 text-gray-500 rounded-lg cursor-not-allowed">Next</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="text-center py-16 px-6">
                <svg class="mx-auto h-16 w-16 text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76"/>
                </svg>
                <h3 class="text-xl font-semibold text-white mb-2">No Unsubscribe Emails Found</h3>
                <p class="text-gray-400">There are no emails with unsubscribe options at the moment.</p>
            </div>
        @endif
    </div>
</div>

@endsection
@push('scripts')
    
<!-- JavaScript for Checkbox Management and Bulk Actions -->
<script>
    $(document).ready(function() {
        let selectedEmails = [];

        // Update bulk actions bar visibility
        function updateBulkActionsBar() {
            const count = selectedEmails.length;
            if (count > 0) {
                $('#bulk-actions-bar').removeClass('hidden');
                $('#selected-count').text(count);
            } else {
                $('#bulk-actions-bar').addClass('hidden');
            }
        }

        // Select All checkbox
        $('#select-all').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('.email-checkbox').prop('checked', isChecked);
            
            if (isChecked) {
                selectedEmails = [];
                $('.email-checkbox').each(function() {
                    selectedEmails.push($(this).data('email-id'));
                });
            } else {
                selectedEmails = [];
            }
            
            updateBulkActionsBar();
        });

        // Individual checkbox
        $('.email-checkbox').on('change', function() {
            const emailId = $(this).data('email-id');
            
            if ($(this).prop('checked')) {
                if (!selectedEmails.includes(emailId)) {
                    selectedEmails.push(emailId);
                }
            } else {
                selectedEmails = selectedEmails.filter(id => id !== emailId);
                $('#select-all').prop('checked', false);
            }
            
            // Update "Select All" checkbox state
            const totalCheckboxes = $('.email-checkbox').length;
            const checkedCheckboxes = $('.email-checkbox:checked').length;
            $('#select-all').prop('checked', totalCheckboxes === checkedCheckboxes && totalCheckboxes > 0);
            
            updateBulkActionsBar();
        });

        // Deselect All button
        $('#deselect-all').on('click', function() {
            $('.email-checkbox').prop('checked', false);
            $('#select-all').prop('checked', false);
            selectedEmails = [];
            updateBulkActionsBar();
        });

        // Bulk Unsubscribe button
        $('#bulk-unsubscribe-btn').on('click', function() {
            if (selectedEmails.length === 0) {
                alert('Please select at least one email.');
                return;
            }

            if (!confirm(`Are you sure you want to automatically unsubscribe from ${selectedEmails.length} email(s)?`)) {
                return;
            }

            $.ajax({
                url: '/api/unsubscribe-emails/bulk',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                data: {
                    email_ids: selectedEmails
                },
                success: function(response) {
                    alert(response.message || 'Bulk unsubscribe initiated successfully!');
                    // TODO: When implemented, reload the page or update the list
                    // location.reload();
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'An error occurred while processing bulk unsubscribe.';
                    alert(errorMsg);
                }
            });
        });

        // Individual Automated Unsubscribe button
        $('.automated-unsubscribe-btn').on('click', function() {
            const emailId = $(this).data('email-id');
            
            if (!confirm('Are you sure you want to automatically unsubscribe from this email?')) {
                return;
            }

            $.ajax({
                url: '/api/unsubscribe-emails/automated',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                data: {
                    email_id: emailId
                },
                success: function(response) {
                    alert(response.message || 'Automated unsubscribe initiated successfully!');
                    // TODO: When implemented, reload the page or remove the row
                    // location.reload();
                },
                error: function(xhr) {
                    const errorMsg = xhr.responseJSON?.message || 'An error occurred while processing automated unsubscribe.';
                    alert(errorMsg);
                }
            });
        });
    });
</script>
@endpush