<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('buyer_id')->constrained('users');
            $table->foreignId('seller_id')->constrained('users');
            $table->enum('status', ['pending_admin', 'ongoing', 'completed', 'cancelled'])
                  ->default('pending_admin');
            $table->bigInteger('admin_message_id')->nullable(); // adminga yuborilgan xabar
            $table->bigInteger('group_chat_id')->nullable();    // deals guruh ID
            $table->bigInteger('topic_id')->nullable();          // forum topic ID
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
