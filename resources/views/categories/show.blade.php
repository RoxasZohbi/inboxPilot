@extends('layouts.dashboard')

@section('title', $category->name)

@section('content')
<div class="mb-6">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('categories.index') }}" 
               class="p-2 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-lg border border-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h2 class="text-4xl font-bold text-white">{{ $category->name }}</h2>
        </div>
        <a href="{{ route('categories.edit', $category) }}" 
           class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-lg border border-gray-700 transition-colors font-medium">
            Edit Category
        </a>
    </div>

    <!-- Category Details Card -->
    <div class="bg-gray-900 rounded-xl p-8 border border-gray-800 shadow-xl mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Priority -->
            <div>
                <h3 class="text-sm font-semibold text-gray-400 mb-3">Importance Level</h3>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium
                        @if($category->priority >= 8) bg-red-900/30 text-red-300 border border-red-700
                        @elseif($category->priority >= 5) bg-blue-900/30 text-blue-300 border border-blue-700
                        @else bg-gray-800 text-gray-300 border border-gray-700
                        @endif">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        {{ $category->priority_label }}
                    </span>
                    <span class="text-gray-500 text-sm">Priority: {{ $category->priority }}/10</span>
                </div>
            </div>

            <!-- Created Date -->
            <div>
                <h3 class="text-sm font-semibold text-gray-400 mb-3">Created</h3>
                <div class="flex items-center gap-2 text-gray-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $category->created_at->format('M d, Y') }}</span>
                    <span class="text-gray-500">•</span>
                    <span class="text-gray-500 text-sm">{{ $category->created_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="mt-8 pt-8 border-t border-gray-800">
            <h3 class="text-sm font-semibold text-gray-400 mb-3">AI Description</h3>
            <div class="bg-gray-800/50 rounded-lg p-6 border border-gray-700">
                <p class="text-gray-300 leading-relaxed whitespace-pre-line">{{ $category->description }}</p>
            </div>
            <p class="mt-3 text-sm text-gray-500">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                This description helps AI understand which emails belong to this category
            </p>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-gray-900 rounded-xl p-6 border border-gray-800 shadow-xl mb-6">
        <form method="GET" action="{{ route('categories.show', $category) }}" class="flex flex-col md:flex-row gap-4">
            <!-- Search Field -->
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search emails by subject, sender, or content..." 
                           class="w-full pl-10 pr-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <!-- Filter: Starred -->
            <div class="flex items-center gap-2 px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg">
                <input type="checkbox" 
                       name="starred" 
                       id="starred" 
                       value="1" 
                       {{ request('starred') === '1' ? 'checked' : '' }}
                       class="w-4 h-4 bg-gray-700 border-gray-600 rounded text-yellow-500 focus:ring-yellow-500 focus:ring-offset-gray-900 focus:ring-2">
                <label for="starred" class="text-sm text-gray-300 cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    Starred Only
                </label>
            </div>

            <!-- Filter: Has Attachments -->
            <div class="flex items-center gap-2 px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg">
                <input type="checkbox" 
                       name="has_attachments" 
                       id="has_attachments" 
                       value="1" 
                       {{ request('has_attachments') === '1' ? 'checked' : '' }}
                       class="w-4 h-4 bg-gray-700 border-gray-600 rounded text-blue-500 focus:ring-blue-500 focus:ring-offset-gray-900 focus:ring-2">
                <label for="has_attachments" class="text-sm text-gray-300 cursor-pointer flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                    With Attachments
                </label>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                Apply Filters
            </button>

            <!-- Clear Filters -->
            @if(request()->hasAny(['search', 'starred', 'has_attachments']))
                <a href="{{ route('categories.show', $category) }}" 
                   class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-lg border border-gray-700 transition-colors font-medium text-center">
                    Clear
                </a>
            @endif
        </form>
    </div>

    <!-- Emails Section -->
    <x-email-list 
        title="Categorized Emails"
        description="Emails that match this category"
        :total-emails="$emails->total()"
        :current-page="$emails->currentPage()"
        :total-pages="$emails->lastPage()"
        :per-page="$emails->perPage()"
        :paginator="$emails">
        <x-slot:emails>
            @forelse($emails as $email)
                <div class="cursor-pointer hover:bg-gray-800/50 transition-colors rounded-lg" 
                     x-data="{}">
                    <div class="flex items-start gap-4 p-4">
                        <!-- Checkbox -->
                        <div class="flex items-center pt-1" @click.stop>
                            <input type="checkbox" 
                                   name="email_ids[]" 
                                   value="{{ $email->id }}"
                                   class="w-4 h-4 bg-gray-800 border-gray-700 rounded text-blue-500 focus:ring-blue-500 focus:ring-offset-gray-900 focus:ring-2">
                        </div>

                        <!-- Email Item Content -->
                        <div class="flex-1 openEmailModal" data-email-id="{{ $email->id }}">
                            <x-email-item 
                                sender="{{ $email->from_name ?? 'Unknown' }}"
                                email="{{ $email->from_email ?? '' }}"
                                subject="{{ $email->subject ?? 'No Subject' }}"
                                preview="{{ $email->ai_summary ?? ($email->snippet ?? 'No preview available') }}"
                                date="{{ $email->created_at ? $email->created_at->diffForHumans() : 'Unknown date' }}"
                                :is-read="!$email->is_unread"
                                :is-starred="!!$email->is_starred"
                                :has-attachment="!!$email->has_attachments"
                                :attachment-count="0"
                                avatar-color="from-purple-500 to-pink-600" />
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-400 mb-2">No Emails Found</h3>
                    <p class="text-gray-500">
                        @if(request()->hasAny(['search', 'starred', 'has_attachments']))
                            Try adjusting your filters or search query
                        @else
                            Emails categorized as "{{ $category->name }}" will appear here
                        @endif
                    </p>
                </div>
            @endforelse
        </x-slot:emails>
    </x-email-list>

    <!-- Email Detail Modal -->
    <x-email-detail-modal />
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        let isModalOpen = false;
        let currentEmailData = null;

        // Open Email Modal on click
        $(document).on('click', '.openEmailModal', function (e) {
            e.stopPropagation();
            const emailId = $(this).data('email-id');
            openModal(emailId);
        });

        // Close modal on background click or close button
        $(document).on('click', '[data-modal-close]', function () {
            closeModal();
        });

        function openModal(emailId) {
            isModalOpen = true;
            $('#emailDetailModal').removeClass('hidden').show();
            $('#modalLoading').show();
            $('#modalContent').hide();

            $.ajax({
                url: `/api/emails/${emailId}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function (response) {
                    currentEmailData = response;
                    populateModal(response);
                    $('#modalLoading').hide();
                    $('#modalContent').show();
                },
                error: function (xhr, status, error) {
                    console.error('Failed to fetch email:', error);
                    $('#modalLoading').hide();
                    $('#modalContent').html('<p class="text-red-400">Failed to load email details</p>').show();
                }
            });
        }

        function closeModal() {
            isModalOpen = false;
            $('#emailDetailModal').fadeOut(200, function () {
                $(this).addClass('hidden');
                currentEmailData = null;
            });
        }

        function populateModal(email) {
            $('#modalSubject').text(email.subject || 'No Subject');
            $('#modalFromName').text(email.from_name || 'Unknown');
            $('#modalFromEmail').text(email.from_email || '');
            $('#modalFromAvatar').text((email.from_name || email.from_email || 'U')[0].toUpperCase());
            $('#modalDate').text(email.date ? new Date(email.date).toLocaleString() : 'Unknown date');
            $('#modalTo').text(email.to || 'Unknown');
            
            // AI Summary
            if (email.ai_summary) {
                $('#modalAiSummary').text(email.ai_summary).parent().show();
            } else {
                $('#modalAiSummary').parent().hide();
            }

            // Starred badge
            if (email.is_starred) {
                $('#modalStarredBadge').show();
            } else {
                $('#modalStarredBadge').hide();
            }

            // Attachments badge
            if (email.has_attachments) {
                $('#modalAttachmentsBadge').show();
            } else {
                $('#modalAttachmentsBadge').hide();
            }

            // Email body
            $('#modalBody').html(email.body || email.snippet || '<p class="text-gray-500">No content available</p>');
        }

        // Close modal on ESC key
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape' && isModalOpen) {
                closeModal();
            }
        });
    });
</script>
@endpush
