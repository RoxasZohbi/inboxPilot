@extends('layouts.dashboard')

@section('title', 'Edit Category')

@section('content')
<div class="max-w-3xl">
    <div class="mb-8">
        <h2 class="text-4xl font-bold text-white mb-3">Edit Category</h2>
        <p class="text-gray-400">Update your category settings</p>
    </div>

    <div class="bg-gray-900 rounded-xl p-8 border border-gray-800 shadow-xl">
        <form method="POST" action="{{ route('categories.update', $category) }}">
            @csrf
            @method('PUT')

            <!-- Name Field -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-semibold text-gray-300 mb-2">
                    Category Name <span class="text-red-400">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       value="{{ old('name', $category->name) }}"
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
                        <option value="{{ $value }}" {{ old('priority', $category->priority) == $value ? 'selected' : '' }}>
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
                          required>{{ old('description', $category->description) }}</textarea>
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

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-800">
                <a href="{{ route('categories.index') }}" 
                   class="px-6 py-3 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-lg border border-gray-700 transition-colors font-medium">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white rounded-lg font-medium transition-all shadow-lg hover:shadow-xl">
                    Update Category
                </button>
            </div>
        </form>
    </div>

    <!-- Delete Section -->
    <div class="mt-8 bg-red-900/10 rounded-xl p-8 border border-red-900/50">
        <h3 class="text-lg font-semibold text-red-300 mb-2">Danger Zone</h3>
        <p class="text-gray-400 text-sm mb-4">Once you delete a category, there is no going back. Please be certain.</p>
        <form method="POST" action="{{ route('categories.destroy', $category) }}" 
              onsubmit="return confirm('Are you sure you want to delete this category? This action cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="px-6 py-3 bg-red-900/30 hover:bg-red-900/50 text-red-300 hover:text-red-200 rounded-lg border border-red-700 transition-colors font-medium">
                Delete Category
            </button>
        </form>
    </div>
</div>
@endsection
