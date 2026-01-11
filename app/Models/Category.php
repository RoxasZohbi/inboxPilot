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
}
