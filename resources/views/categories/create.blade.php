@extends('layouts.dashboard')

@section('title', 'Create Category')

@section('content')
<div class="max-w-3xl">
    <div class="mb-8">
        <h2 class="text-4xl font-bold text-white mb-3">Create New Category</h2>
        <p class="text-gray-400">Set up a new category to organize your emails</p>
    </div>

    <div class="bg-gray-900 rounded-xl p-8 border border-gray-800 shadow-xl">
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf

            <!-- Name Field -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-300 mb-2">
                    Category Name <span class="text-red-400">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name') }}"
                       placeholder="e.g., Invoices, Newsletters, Important Clients"
                       class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                       required>
                @error('name')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Priority Field -->
            <div class="mb-6">
                <label for="priority" class="block text-sm font-semibold text-gray-300 mb-2">
                    Importance Level <span class="text-red-400">*</span>
                </label>
                <select name="priority" 
                        id="priority" 
                        class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        required>
                    <option value="">Select importance level...</option>
                    @foreach($priorityLevels as $value => $label)
                        <option value="{{ $value }}" {{ old('priority') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-2 text-sm text-gray-500">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    If an email matches multiple categories, the one with higher importance wins
                </p>
                @error('priority')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description Field -->
            <div class="mb-8">
                <label for="description" class="block text-sm font-semibold text-gray-300 mb-2">
                    AI Description <span class="text-red-400">*</span>
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="5"
                          placeholder="Describe when emails should be categorized here. Be specific to help AI understand..."
                          class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                          required>{{ old('description') }}</textarea>
                <div class="mt-2 bg-blue-900/20 border border-blue-800 rounded-lg p-4">
                    <p class="text-sm text-blue-300 font-medium mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                        Tip: Help AI understand your category
                    </p>
                    <ul class="text-sm text-gray-400 space-y-1 ml-5 list-disc">
                        <li>Be specific about email types (e.g., "Monthly invoices from vendors and suppliers")</li>
                        <li>Mention sender patterns (e.g., "Emails from @company.com domain")</li>
                        <li>Describe content keywords (e.g., "Contains words like 'payment due', 'invoice', or 'billing'")</li>
                        <li>Note subject patterns (e.g., "Subject line starts with [URGENT] or [ACTION REQUIRED]")</li>
                    </ul>
                </div>
                @error('description')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Archive After Processing Checkbox -->
            <div class="mb-8">
                <div class="bg-purple-900/10 rounded-xl p-6 border border-purple-900/50">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" 
                                   name="archive_after_processing" 
                                   id="archive_after_processing" 
                                   value="1"
                                   {{ old('archive_after_processing') ? 'checked' : '' }}
                                   class="w-5 h-5 bg-gray-800 border-gray-700 rounded text-purple-600 focus:ring-purple-500 focus:ring-offset-gray-900 focus:ring-2 cursor-pointer">
                        </div>
                        <div class="ml-4">
                            <label for="archive_after_processing" class="text-sm font-semibold text-gray-300 cursor-pointer">
                                Auto-Archive After Processing
                            </label>
                            <p class="mt-1 text-sm text-gray-400">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                </svg>
                                Emails in this category will be automatically archived in Gmail after successful AI processing (remains accessible in All Mail)
                            </p>
                        </div>
                    </div>
                    @error('archive_after_processing')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-800">
                <a href="{{ route('categories.index') }}" 
                   class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-lg border border-gray-700 transition-colors font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white rounded-lg font-medium transition-all shadow-lg hover:shadow-xl">
                    Create Category
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
