@props(['email'])

<div id="emailDetailModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" data-modal-close></div>

    <!-- Modal panel -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-4xl bg-gray-900 rounded-xl shadow-2xl border border-gray-800">
            
            <!-- Loading State -->
            <div id="modalLoading" class="p-12 text-center" style="display: none;">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                <p class="mt-4 text-gray-400">Loading email...</p>
            </div>

            <!-- Email Content -->
            <div id="modalContent" class="flex flex-col max-h-[90vh]" style="display: none;">
                <!-- Header -->
                <div class="flex items-start justify-between p-6 border-b border-gray-800">
                    <div class="flex-1 pr-4">
                        <h3 id="modalSubject" class="text-2xl font-bold text-white mb-2">No Subject</h3>
                        <div class="flex items-center gap-4 text-sm text-gray-400">
                            <div class="flex items-center gap-2">
                                <div id="modalFromAvatar" class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">U</div>
                                <div>
                                    <p id="modalFromName" class="text-white font-medium">Unknown</p>
                                    <p id="modalFromEmail" class="text-gray-500 text-xs"></p>
                                </div>
                            </div>
                            <span>•</span>
                            <span id="modalDate">Unknown date</span>
                        </div>
                    </div>
                    <button data-modal-close class="p-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Email Meta Info -->
                <div class="px-6 py-4 bg-gray-800/50 border-b border-gray-800">
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <!-- To -->
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">To:</span>
                            <span id="modalTo" class="text-gray-300">Unknown</span>
                        </div>

                        <!-- Labels/Tags -->
                        <span id="modalStarredBadge" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-900/30 text-yellow-300 border border-yellow-700" style="display: none;">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            Starred
                        </span>

                        <span id="modalAttachmentsBadge" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-900/30 text-blue-300 border border-blue-700" style="display: none;">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            Has Attachments
                        </span>
                    </div>
                </div>

                <!-- AI Summary (if available) -->
                <div class="px-6 py-4 bg-blue-900/10 border-b border-gray-800" style="display: none;">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-300 mb-1">AI Summary</h4>
                            <p id="modalAiSummary" class="text-gray-300 text-sm"></p>
                        </div>
                    </div>
                </div>

                <!-- Email Body -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="prose prose-invert max-w-none">
                        <div id="modalBody"></div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="px-6 py-4 border-t border-gray-800 flex items-center justify-end gap-3">
                    <button data-modal-close class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-lg border border-gray-700 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
