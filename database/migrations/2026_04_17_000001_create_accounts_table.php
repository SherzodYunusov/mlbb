<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('heroes_count');
            $table->unsignedInteger('skins_count');
            $table->string('collection_level');
            $table->text('description')->nullable();
            $table->boolean('ready_for_transfer')->default(false);
            $table->enum('status', ['draft', 'pending', 'active', 'rejected', 'sold'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
