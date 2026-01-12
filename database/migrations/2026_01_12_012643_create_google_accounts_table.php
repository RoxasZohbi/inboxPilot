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
        Schema::create('google_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('google_id')->unique();
            $table->string('email')->index();
            $table->string('name')->nullable();
            $table->string('avatar')->nullable();
            $table->text('google_token');
            $table->text('google_refresh_token');
            $table->timestamp('last_synced_at')->nullable();
            $table->tinyInteger('is_primary')->default(0);
            $table->timestamps();
            
            // Composite index for efficient querying
            $table->index(['user_id', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('google_accounts');
    }
};
