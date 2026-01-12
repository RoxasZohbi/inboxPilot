<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Email extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'google_account_id',
        'gmail_id',
        'thread_id',
        'category_id',
        'subject',
        'from_email',
        'from_name',
        'to',
        'date',
        'body',
        'snippet',
        'ai_summary',
        'status',
        'failed_reason',
        'labels',
        'is_unread',
        'is_starred',
        'is_archived',
        'has_attachments',
        'internal_date',
        'is_unsubscribe_available',
        'unsubscribe_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'labels' => 'array',
            'date' => 'datetime',
            'internal_date' => 'datetime',
            'processed_at' => 'datetime',
            // 'is_archived' => 'boolean',
            // 'is_unread' => 'boolean',
            // 'is_starred' => 'boolean',
            // 'has_attachments' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the email.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the Google account that owns the email.
     */
    public function googleAccount(): BelongsTo
    {
        return $this->belongsTo(GoogleAccount::class);
    }

    /**
     * Get the category that the email belongs to.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Scope a query to only include unread emails.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_unread', true);
    }

    /**
     * Scope a query to only include starred emails.
     */
    public function scopeStarred($query)
    {
        return $query->where('is_starred', true);
    }

    /**
     * Scope a query to only include emails with attachments.
     */
    public function scopeWithAttachments($query)
    {
        return $query->where('has_attachments', true);
    }

    /**
     * Scope a query to only include emails with unsubscribe available.
     */
    public function scopeWithUnsubscribe($query)
    {
        return $query->where('is_unsubscribe_available', 1);
    }
}
