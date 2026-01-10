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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('gmail_id')->unique();
            $table->string('thread_id')->index();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('subject')->nullable();
            $table->string('from_email')->index();
            $table->string('from_name')->nullable();
            $table->string('to')->nullable();
            $table->timestamp('date')->nullable()->index();
            
            $table->longText('body')->nullable();
            $table->text('snippet')->nullable();
            $table->json('labels')->nullable();
            
            $table->tinyInteger('is_unread')->default(1)->index();
            $table->tinyInteger('is_starred')->default(0);
            $table->tinyInteger('has_attachments')->default(0);
            
            $table->timestamp('internal_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Composite index for user's unread emails
            $table->index(['user_id', 'is_unread']);
            // Index for date-based queries
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
