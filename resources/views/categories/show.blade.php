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
    <x-email-list 
        title="Categorized Emails"
        description="Emails that match this category"
        :total-emails="247"
        :current-page="1"
        :total-pages="83"
        :per-page="3">
        <x-slot:emails>
            <x-email-item 
                sender="John Doe"
                email="john.doe@company.com"
                subject="Q4 Financial Report - Action Required"
                preview="Hello team, please find attached the Q4 financial report for your review. We need your feedback by end of this week..."
                date="2 hours ago"
                :is-read="false"
                :has-attachment="true"
                avatar-color="from-blue-500 to-purple-600" />

            <x-email-item 
                sender="Sarah Miller"
                email="sarah.miller@email.com"
                subject="Meeting Notes from Monday's Discussion"
                preview="Hi everyone, here are the key takeaways from our meeting on Monday. Please review and let me know if I missed anything..."
                date="Yesterday"
                :is-read="true"
                :is-starred="true"
                avatar-color="from-green-500 to-emerald-600" />

            <x-email-item 
                sender="Michael Johnson"
                email="m.johnson@business.com"
                subject="Project Update - Phase 2 Complete"
                preview="Great news! We've successfully completed Phase 2 of the project ahead of schedule. Here's a summary of what we accomplished..."
                date="Jan 8"
                :is-read="true"
                :has-attachment="true"
                :attachment-count="2"
                avatar-color="from-purple-500 to-pink-600" />
        </x-slot:emails>
    </x-email-list>
</div>
@endsection
