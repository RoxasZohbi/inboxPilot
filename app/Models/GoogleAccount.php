<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoogleAccount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'google_id',
        'email',
        'name',
        'avatar',
        'google_token',
        'google_refresh_token',
        'last_synced_at',
        'is_primary',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [
        'google_token',
        'google_refresh_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
            'is_primary' => 'boolean',
        ];
    }

    /**
     * Get the user that owns the Google account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the emails for the Google account.
     */
    public function emails(): HasMany
    {
        return $this->hasMany(Email::class);
    }

    /**
     * Get the count of unread emails.
     */
    public function unreadEmailsCount(): int
    {
        return $this->emails()->unread()->count();
    }

    /**
     * Get the total count of emails.
     */
    public function totalEmailsCount(): int
    {
        return $this->emails()->count();
    }

    /**
     * Scope a query to only include primary accounts.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Set this account as primary and unset others for the same user.
     */
    public function makePrimary(): void
    {
        // Unset primary flag for all other accounts of this user
        static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        // Set this account as primary
        $this->update(['is_primary' => true]);
    }
}
