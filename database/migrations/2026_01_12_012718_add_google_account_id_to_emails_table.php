<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->foreignId('google_account_id')->nullable()->after('user_id')->constrained('google_accounts')->onDelete('cascade');
            
            // Drop old unique constraint on gmail_id
            $table->dropUnique(['gmail_id']);
            
            // Add composite unique constraint (google_account_id, gmail_id)
            $table->unique(['google_account_id', 'gmail_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            // Drop composite unique constraint
            $table->dropUnique(['google_account_id', 'gmail_id']);
            
            // Restore original unique constraint on gmail_id
            $table->unique('gmail_id');
            
            // Drop foreign key and column
            $table->dropForeign(['google_account_id']);
            $table->dropColumn('google_account_id');
        });
    }
};
