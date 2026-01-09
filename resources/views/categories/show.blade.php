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

    <!-- Emails Section -->
    <div class="bg-gray-900 rounded-xl border border-gray-800 shadow-xl">
        <div class="p-6 border-b border-gray-800">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-2xl font-bold text-white mb-2">Categorized Emails</h3>
                    <p class="text-gray-400 text-sm">Emails that match this category</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Search -->
                    <div class="relative">
                        <input type="text" 
                               placeholder="Search emails..."
                               class="w-64 px-4 py-2 pl-10 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    
                    <!-- Filter -->
                    <select class="px-4 py-2 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        <option>All Time</option>
                        <option>Today</option>
                        <option>Last 7 Days</option>
                        <option>Last 30 Days</option>
                        <option>This Year</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Email List -->
        <div class="divide-y divide-gray-800">
            <!-- Sample Email 1 -->
            <div class="p-6 hover:bg-gray-800/50 transition-colors cursor-pointer group">
                <div class="flex items-start gap-4">
                    
                    <!-- Email Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <!-- Avatar -->
                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                                    JD
                                </div>
                                <!-- Sender -->
                                <div>
                                    <h4 class="text-white font-semibold group-hover:text-blue-400 transition-colors">John Doe</h4>
                                    <p class="text-gray-500 text-sm">john.doe@company.com</p>
                                </div>
                            </div>
                            <!-- Date & Actions -->
                            <div class="flex items-center gap-4">
                                <span class="text-gray-500 text-sm">2 hours ago</span>
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="p-2 text-gray-400 hover:text-blue-400 hover:bg-gray-700 rounded transition-colors" title="Star">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    </button>
                                    <button class="p-2 text-gray-400 hover:text-red-400 hover:bg-gray-700 rounded transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Subject -->
                        <h5 class="text-white font-medium mb-2 group-hover:text-blue-400 transition-colors">
                            Q4 Financial Report - Action Required
                        </h5>
                        
                        <!-- Preview -->
                        <p class="text-gray-400 text-sm line-clamp-2 mb-3">
                            Hello team, please find attached the Q4 financial report for your review. We need your feedback by end of this week...
                        </p>
                        
                        <!-- Tags -->
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-900/30 text-blue-300 border border-blue-700">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                Has Attachment
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-900/30 text-orange-300 border border-orange-700">
                                Unread
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sample Email 2 -->
            <div class="p-6 hover:bg-gray-800/50 transition-colors cursor-pointer group">
                <div class="flex items-start gap-4">
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-green-500 to-emerald-600 flex items-center justify-center text-white font-semibold text-sm">
                                    SM
                                </div>
                                <div>
                                    <h4 class="text-gray-400 font-semibold">Sarah Miller</h4>
                                    <p class="text-gray-500 text-sm">sarah.miller@email.com</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-gray-500 text-sm">Yesterday</span>
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="p-2 text-yellow-400 hover:text-yellow-300 hover:bg-gray-700 rounded transition-colors" title="Star">
                                        <svg class="w-4 h-4" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    </button>
                                    <button class="p-2 text-gray-400 hover:text-red-400 hover:bg-gray-700 rounded transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="text-gray-400 font-medium mb-2">
                            Meeting Notes from Monday's Discussion
                        </h5>
                        
                        <p class="text-gray-500 text-sm line-clamp-2 mb-3">
                            Hi everyone, here are the key takeaways from our meeting on Monday. Please review and let me know if I missed anything...
                        </p>
                        
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-900/30 text-green-300 border border-green-700">
                                Read
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sample Email 3 -->
            <div class="p-6 hover:bg-gray-800/50 transition-colors cursor-pointer group">
                <div class="flex items-start gap-4">
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-500 to-pink-600 flex items-center justify-center text-white font-semibold text-sm">
                                    MJ
                                </div>
                                <div>
                                    <h4 class="text-gray-400 font-semibold">Michael Johnson</h4>
                                    <p class="text-gray-500 text-sm">m.johnson@business.com</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-gray-500 text-sm">Jan 8</span>
                                <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="p-2 text-gray-400 hover:text-blue-400 hover:bg-gray-700 rounded transition-colors" title="Star">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                        </svg>
                                    </button>
                                    <button class="p-2 text-gray-400 hover:text-red-400 hover:bg-gray-700 rounded transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="text-gray-400 font-medium mb-2">
                            Project Update - Phase 2 Complete
                        </h5>
                        
                        <p class="text-gray-500 text-sm line-clamp-2 mb-3">
                            Great news! We've successfully completed Phase 2 of the project ahead of schedule. Here's a summary of what we accomplished...
                        </p>
                        
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-900/30 text-blue-300 border border-blue-700">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                2 Attachments
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-900/30 text-green-300 border border-green-700">
                                Read
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State (uncomment when no emails) -->
            <!-- <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-400 mb-2">No emails yet</h3>
                <p class="text-gray-500">Emails matching this category will appear here</p>
            </div> -->
        </div>

        <!-- Pagination -->
        <div class="p-6 border-t border-gray-800">
            <div class="flex items-center justify-between">
                <p class="text-gray-400 text-sm">Showing 1-3 of 247 emails</p>
                <div class="flex items-center gap-2">
                    <button class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-400 rounded-lg border border-gray-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        Previous
                    </button>
                    <button class="px-4 py-2 bg-gray-800 text-white rounded-lg border border-gray-700">1</button>
                    <button class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-400 rounded-lg border border-gray-700 transition-colors">2</button>
                    <button class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-400 rounded-lg border border-gray-700 transition-colors">3</button>
                    <span class="px-2 text-gray-500">...</span>
                    <button class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-400 rounded-lg border border-gray-700 transition-colors">83</button>
                    <button class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-400 rounded-lg border border-gray-700 transition-colors">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
