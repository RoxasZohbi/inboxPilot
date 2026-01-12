<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\GoogleAccount;
use App\Models\Email;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate existing user tokens to google_accounts table
        $users = User::whereNotNull('google_token')
            ->whereNotNull('google_id')
            ->get();

        foreach ($users as $user) {
            // Check if account already exists (in case migration is run multiple times)
            $existingAccount = GoogleAccount::where('google_id', $user->google_id)->first();
            
            if (!$existingAccount) {
                // Create Google account from user data
                $googleAccount = GoogleAccount::create([
                    'user_id' => $user->id,
                    'google_id' => $user->google_id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'avatar' => $user->avatar,
                    'google_token' => $user->google_token,
                    'google_refresh_token' => $user->google_refresh_token,
                    'last_synced_at' => $user->last_synced_at,
                    'is_primary' => true, // First migrated account is primary
                ]);

                // Update all emails for this user to link to the new Google account
                Email::where('user_id', $user->id)
                    ->whereNull('google_account_id')
                    ->update(['google_account_id' => $googleAccount->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove google_account_id from emails (set to null)
        Email::whereNotNull('google_account_id')->update(['google_account_id' => null]);
        
        // Delete all Google accounts created from users
        GoogleAccount::truncate();
    }
};
