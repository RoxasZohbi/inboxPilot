@props([
    'title' => 'Categorized Emails',
    'description' => 'Emails that match this category',
    'showSearch' => false,
    'showFilter' => false,
    'emptyTitle' => 'No emails yet',
    'emptyDescription' => 'Emails matching this category will appear here',
    'showPagination' => true,
    'currentPage' => 1,
    'totalPages' => 1,
    'totalEmails' => 0,
    'perPage' => 10,
    'paginator' => null,
])

<div class="bg-gray-900 rounded-xl border border-gray-800 shadow-xl">
    <!-- Bulk Actions Bar (Hidden by default, shown when emails selected) -->
    <div id="bulkActionsBar" class="hidden bg-blue-900/30 border-b border-blue-700 p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <input type="checkbox" 
                       id="selectAllCheckbox"
                       class="w-5 h-5 bg-gray-800 border-gray-700 rounded text-blue-500 focus:ring-blue-500 focus:ring-offset-gray-900 focus:ring-2 cursor-pointer">
                <label for="selectAllCheckbox" class="text-white font-medium cursor-pointer">
                    Select All (<span id="selectedCount">0</span> selected)
                </label>
            </div>
            <button id="bulkDeleteBtn" 
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete Selected
            </button>
        </div>
    </div>

    <!-- Header -->
    <div class="p-6 border-b border-gray-800">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-bold text-white mb-2">{{ $title }}</h3>
                <p class="text-gray-400 text-sm">{{ $description }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if($showSearch)
                    <!-- Search -->
                    <div class="relative">
                        <input type="text" 
                               placeholder="Search emails..."
                               class="w-64 px-4 py-2 pl-10 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                @endif
                
                @if($showFilter)
                    <!-- Filter -->
                    <select class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option>All Time</option>
                        <option>Today</option>
                        <option>Last 7 Days</option>
                        <option>Last 30 Days</option>
                        <option>This Year</option>
                    </select>
                @endif


            </div>
        </div>
    </div>

    <!-- Email List -->
    <div class="divide-y divide-gray-800">
        @if(isset($emails))
            {{ $emails }}
        @else
            <!-- Empty State -->
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-400 mb-2">{{ $emptyTitle }}</h3>
                <p class="text-gray-500">{{ $emptyDescription }}</p>
            </div>
        @endif
    </div>

    @if($showPagination && $paginator && $paginator->hasPages())
        <!-- Pagination -->
        <div class="p-6 border-t border-gray-800">
            {{ $paginator->appends(request()->query())->links() }}
        </div>
    @endif
</div>
