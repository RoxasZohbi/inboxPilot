<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'priority',
        'name',
        'description',
        'archive_after_processing',
    ];

    /**
     * Priority levels for email categorization.
     * Higher numbers = higher priority (wins when multiple categories match).
     */
    const PRIORITY_CRITICAL = 10;
    const PRIORITY_VERY_HIGH = 9;
    const PRIORITY_HIGH = 8;
    const PRIORITY_ELEVATED = 7;
    const PRIORITY_ABOVE_NORMAL = 6;
    const PRIORITY_NORMAL = 5;
    const PRIORITY_BELOW_NORMAL = 4;
    const PRIORITY_LOW = 3;
    const PRIORITY_VERY_LOW = 2;
    const PRIORITY_MINIMAL = 1;

    /**
     * Get all available priority levels.
     *
     * @return array<int, string>
     */
    public static function getPriorityLevels(): array
    {
        return [
            self::PRIORITY_CRITICAL => 'Critical (Highest)',
            self::PRIORITY_VERY_HIGH => 'Very High',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_ELEVATED => 'Elevated',
            self::PRIORITY_ABOVE_NORMAL => 'Above Normal',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_BELOW_NORMAL => 'Below Normal',
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_VERY_LOW => 'Very Low',
            self::PRIORITY_MINIMAL => 'Minimal (Lowest)',
        ];
    }

    /**
     * Get priority label by value.
     *
     * @param int $priority
     * @return string
     */
    public static function getPriorityLabel(int $priority): string
    {
        return self::getPriorityLevels()[$priority] ?? 'Unknown';
    }

    /**
     * Get the priority label for this category.
     *
     * @return string
     */
    public function getPriorityLabelAttribute(): string
    {
        return self::getPriorityLabel($this->priority);
    }

    /**
     * Get the user that owns the category.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the emails for this category.
     */
    public function emails()
    {
        return $this->hasMany(Email::class);
    }

    /**
     * Get the total count of emails in this category.
     *
     * @return int
     */
    public function emailsCount(): int
    {
        return $this->emails()->count();
    }

    /**
     * Get the count of unread emails in this category.
     *
     * @return int
     */
    public function unreadEmailsCount(): int
    {
        return $this->emails()->where('is_read', false)->count();
    }

    /**
     * Get recent emails for this category.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function recentEmails(int $limit = 3)
    {
        return $this->emails()->latest()->limit($limit)->get();
    }

    /**
     * Get the gradient color class based on priority.
     *
     * @return string
     */
    public function getCardColorAttribute(): string
    {
        return match(true) {
            $this->priority >= 9 => 'from-red-500 to-orange-500',
            $this->priority >= 7 => 'from-orange-500 to-yellow-500',
            $this->priority >= 5 => 'from-blue-500 to-cyan-500',
            $this->priority >= 3 => 'from-purple-500 to-pink-500',
            default => 'from-green-500 to-emerald-500',
        };
    }

    /**
     * Get the default icon based on category name or priority.
     *
     * @return string
     */
    public function getCardIconAttribute(): string
    {
        $name = strtolower($this->name);
        
        // Match common category names
        if (str_contains($name, 'work') || str_contains($name, 'job')) {
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>';
        }
        if (str_contains($name, 'shop') || str_contains($name, 'order') || str_contains($name, 'purchase')) {
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>';
        }
        if (str_contains($name, 'personal') || str_contains($name, 'family') || str_contains($name, 'friend')) {
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>';
        }
        if (str_contains($name, 'urgent') || str_contains($name, 'important') || str_contains($name, 'critical')) {
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>';
        }
        if (str_contains($name, 'social') || str_contains($name, 'media')) {
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>';
        }
        if (str_contains($name, 'finance') || str_contains($name, 'bank') || str_contains($name, 'bill')) {
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>';
        }
        if (str_contains($name, 'travel') || str_contains($name, 'trip') || str_contains($name, 'flight')) {
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>';
        }
        if (str_contains($name, 'news') || str_contains($name, 'newsletter')) {
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>';
        }
        
        // Default icon based on priority
        if ($this->priority >= 8) {
            return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>';
        }
        
        // Default tag icon
        return '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>';
    }
}
