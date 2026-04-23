<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->string('contact', 100)->nullable();   // Telegram username
            $table->enum('status', ['pending', 'active', 'closed'])->default('pending');
            $table->integer('admin_message_id')->nullable();
            $table->timestamps();
        });

        Schema::create('account_request_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')
                  ->constrained('account_requests')
                  ->cascadeOnDelete();
            $table->foreignId('sender_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->text('message');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_request_comments');
        Schema::dropIfExists('account_requests');
    }
};
