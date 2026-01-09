@extends('layouts.dashboard')

@section('title', 'Categories')

@section('content')
<div class="mb-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-4xl font-bold text-white mb-3">Email Categories</h2>
            <p class="text-gray-400">Organize your emails with AI-powered categories</p>
        </div>
        <a href="{{ route('categories.create') }}" 
           class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white rounded-lg font-medium transition-all shadow-lg hover:shadow-xl">
            + Create Category
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 px-6 py-4 bg-green-900/30 border border-green-700 text-green-300 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($categories->isEmpty())
        <div class="bg-gray-900 rounded-xl p-12 text-center border border-gray-800">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            <h3 class="text-xl font-semibold text-gray-300 mb-2">No categories yet</h3>
            <p class="text-gray-500 mb-6">Create your first category to start organizing emails</p>
            <a href="{{ route('categories.create') }}" 
               class="inline-flex items-center px-6 py-3 bg-gray-800 hover:bg-gray-700 text-white rounded-lg font-medium transition-colors">
                Get Started
            </a>
        </div>
    @else
        <div class="bg-gray-900 rounded-xl overflow-hidden border border-gray-800 shadow-xl">
            <table class="w-full">
                <thead class="bg-gray-800 border-b border-gray-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Name</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Importance</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-300">Description</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($categories as $category)
                        <tr class="hover:bg-gray-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="text-white font-medium">{{ $category->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                    @if($category->priority >= 8) bg-red-900/30 text-red-300 border border-red-700
                                    @elseif($category->priority >= 5) bg-blue-900/30 text-blue-300 border border-blue-700
                                    @else bg-gray-800 text-gray-300 border border-gray-700
                                    @endif">
                                    {{ $category->priority_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-gray-400 text-sm line-clamp-2">
                                    {{ Str::limit($category->description, 80) }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('categories.show', $category) }}" 
                                       class="px-4 py-2 bg-blue-900/30 hover:bg-blue-900/50 text-blue-300 hover:text-blue-200 rounded-lg border border-blue-700 transition-colors font-medium text-sm">
                                        View
                                    </a>
                                    <a href="{{ route('categories.edit', $category) }}" 
                                       class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white rounded-lg border border-gray-700 transition-colors font-medium text-sm">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}" 
                                          onsubmit="return confirm('Are you sure you want to delete this category?');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-4 py-2 bg-red-900/30 hover:bg-red-900/50 text-red-300 hover:text-red-200 rounded-lg border border-red-700 transition-colors font-medium text-sm">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6 text-center text-gray-500 text-sm">
            Total: {{ $categories->count() }} {{ Str::plural('category', $categories->count()) }}
        </div>
    @endif
</div>
@endsection
