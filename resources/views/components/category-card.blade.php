@props(['category'])

<a href="{{ route('categories.show', $category) }}" class="bg-gray-800 border border-gray-700 rounded-lg p-5 hover:border-gray-600 transition-all duration-300 hover:shadow-lg group block">
    <div class="flex items-start justify-between mb-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br {{ $category->card_color }} rounded-lg flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $category->card_icon !!}
                </svg>
            </div>
            <div>
                <h3 class="text-white font-semibold">{{ $category->name }}</h3>
                <p class="text-gray-400 text-xs">{{ $category->emails_count ?? 0 }} {{ Str::plural('email', $category->emails_count ?? 0) }}</p>
            </div>
        </div>
        <button onclick="event.preventDefault(); event.stopPropagation(); /* Add menu functionality */" class="opacity-0 group-hover:opacity-100 text-gray-400 hover:text-white transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
            </svg>
        </button>
    </div>
    <p class="text-gray-400 text-sm mb-3 line-clamp-2">{{ $category->description }}</p>
    <div class="flex items-center gap-2 flex-wrap">
        <span class="text-xs bg-blue-900/30 text-blue-400 px-2 py-1 rounded border border-blue-800">
            Priority: {{ $category->priority }}
        </span>
        <span class="text-xs bg-green-900/30 text-green-400 px-2 py-1 rounded border border-green-800">
            Active
        </span>
        @if($category->archive_after_processing)
            <span class="text-xs bg-purple-900/30 text-purple-400 px-2 py-1 rounded border border-purple-800">
                Auto-archive
            </span>
        @endif
    </div>
</a>
