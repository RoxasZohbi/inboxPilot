@props(['email'])

<div x-data="emailDetailModal()" 
     @open-email-modal.window="openModal($event.detail.emailId)"
     x-show="isOpen" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto" 
     aria-labelledby="modal-title" 
     role="dialog" 
     aria-modal="true">
    
    <!-- Background overlay -->
    <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" 
         @click="closeModal()"
         x-show="isOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"></div>

    <!-- Modal panel -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-4xl bg-gray-900 rounded-xl shadow-2xl border border-gray-800"
             x-show="isOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.away="closeModal()">
            
            <!-- Loading State -->
            <div x-show="loading" class="p-12 text-center">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
                <p class="mt-4 text-gray-400">Loading email...</p>
            </div>

            <!-- Email Content -->
            <div x-show="!loading && emailData" class="flex flex-col max-h-[90vh]">
                <!-- Header -->
                <div class="flex items-start justify-between p-6 border-b border-gray-800">
                    <div class="flex-1 pr-4">
                        <h3 class="text-2xl font-bold text-white mb-2" x-text="emailData?.subject || 'No Subject'"></h3>
                        <div class="flex items-center gap-4 text-sm text-gray-400">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm"
                                     x-text="(emailData?.from_name || emailData?.from_email || 'U')[0].toUpperCase()"></div>
                                <div>
                                    <p class="text-white font-medium" x-text="emailData?.from_name || 'Unknown'"></p>
                                    <p class="text-gray-500 text-xs" x-text="emailData?.from_email"></p>
                                </div>
                            </div>
                            <span>•</span>
                            <span x-text="emailData?.date ? new Date(emailData.date).toLocaleString() : 'Unknown date'"></span>
                        </div>
                    </div>
                    <button @click="closeModal()" 
                            class="p-2 hover:bg-gray-800 rounded-lg transition-colors">
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
                            <span class="text-gray-300" x-text="emailData?.to || 'Unknown'"></span>
                        </div>

                        <!-- Labels/Tags -->
                        <template x-if="emailData?.is_starred">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-900/30 text-yellow-300 border border-yellow-700">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                Starred
                            </span>
                        </template>

                        <template x-if="emailData?.has_attachments">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-900/30 text-blue-300 border border-blue-700">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                Has Attachments
                            </span>
                        </template>
                    </div>
                </div>

                <!-- AI Summary (if available) -->
                <div x-show="emailData?.ai_summary" class="px-6 py-4 bg-blue-900/10 border-b border-gray-800">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        <div>
                            <h4 class="text-sm font-semibold text-blue-300 mb-1">AI Summary</h4>
                            <p class="text-gray-300 text-sm" x-text="emailData?.ai_summary"></p>
                        </div>
                    </div>
                </div>

                <!-- Email Body -->
                <div class="flex-1 overflow-y-auto p-6">
                    <div class="prose prose-invert max-w-none">
                        <div x-html="emailData?.body || emailData?.snippet || '<p class=\'text-gray-500\'>No content available</p>'"></div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="px-6 py-4 border-t border-gray-800 flex items-center justify-end gap-3">
                    <button @click="closeModal()" 
                            class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-lg border border-gray-700 transition-colors">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function emailDetailModal() {
    return {
        isOpen: false,
        loading: false,
        emailData: null,

        async openModal(emailId) {
            this.isOpen = true;
            this.loading = true;
            this.emailData = null;

            try {
                const response = await fetch(`/api/emails/${emailId}`);
                if (response.ok) {
                    this.emailData = await response.json();
                } else {
                    console.error('Failed to fetch email');
                }
            } catch (error) {
                console.error('Error fetching email:', error);
            } finally {
                this.loading = false;
            }
        },

        closeModal() {
            this.isOpen = false;
            setTimeout(() => {
                this.emailData = null;
            }, 200);
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
