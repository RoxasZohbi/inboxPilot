@props([
    'sender' => 'Unknown',
    'email' => '',
    'subject' => 'No Subject',
    'preview' => '',
    'date' => 'Unknown',
    'isRead' => false,
    'isStarred' => false,
    'hasAttachment' => false,
    'attachmentCount' => 0,
    'avatarColor' => 'from-blue-500 to-purple-600',
])

<div class="p-6 hover:bg-gray-800/50 transition-colors cursor-pointer group">
    <div class="flex items-start gap-4">
        <!-- Email Content -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-3">
                    <!-- Avatar -->
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r {{ $avatarColor }} flex items-center justify-center text-white font-semibold text-sm">
                        {{ strtoupper(substr($sender, 0, 1)) }}{{ strtoupper(substr(explode(' ', $sender)[1] ?? '', 0, 1)) }}
                    </div>
                    <!-- Sender -->
                    <div>
                        <h4 class="font-semibold {{ $isRead ? 'text-gray-400' : 'text-white group-hover:text-blue-400' }} transition-colors">
                            {{ $sender }}
                        </h4>
                        @if($email)
                            <p class="text-gray-500 text-sm">{{ $email }}</p>
                        @endif
                    </div>
                </div>
                <!-- Date & Actions -->
                <div class="flex items-center gap-4">
                    <span class="text-gray-500 text-sm">{{ $date }}</span>
                    <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button class="p-2 hover:bg-gray-700 rounded transition-colors {{ $isStarred ? 'text-yellow-400 hover:text-yellow-300' : 'text-gray-400 hover:text-blue-400' }}" title="Star">
                            <svg class="w-4 h-4" fill="{{ $isStarred ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
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
            <h5 class="font-medium mb-2 {{ $isRead ? 'text-gray-400' : 'text-white group-hover:text-blue-400' }} transition-colors">
                {{ $subject }}
            </h5>
            
            <!-- Preview -->
            @if($preview)
                <p class="{{ $isRead ? 'text-gray-500' : 'text-gray-400' }} text-sm mb-3">
                    {{ $preview }}
                </p>
            @endif
            
            <!-- Tags -->
            <div class="flex items-center gap-2">
                @if($hasAttachment)
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-900/30 text-blue-300 border border-blue-700">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                        @if($attachmentCount > 1)
                            {{ $attachmentCount }} Attachments
                        @else
                            Has Attachment
                        @endif
                    </span>
                @endif
                
                @if($isRead)
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-900/30 text-green-300 border border-green-700">
                        Read
                    </span>
                @else
                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-900/30 text-orange-300 border border-orange-700">
                        Unread
                    </span>
                @endif

                {{ $slot }}
            </div>
        </div>
    </div>
</div>
