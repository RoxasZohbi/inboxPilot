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
            $table->text('ai_summary')->nullable()->after('snippet');
            $table->string('status')->default('pending')->index()->after('ai_summary');
            $table->text('failed_reason')->nullable()->after('status');
            $table->timestamp('processed_at')->nullable()->index()->after('failed_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->dropColumn(['ai_summary', 'status', 'failed_reason', 'processed_at']);
        });
    }
};
