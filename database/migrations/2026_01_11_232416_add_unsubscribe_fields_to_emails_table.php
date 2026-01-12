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
            $table->tinyInteger('is_unsubscribe_available')->default(0)->after('ai_summary');
            $table->text('unsubscribe_url')->nullable()->after('is_unsubscribe_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn(['is_unsubscribe_available', 'unsubscribe_url']);
        });
    }
};
