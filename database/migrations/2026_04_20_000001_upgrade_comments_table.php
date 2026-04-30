<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('sender_id')
                  ->constrained('comments')->nullOnDelete();
            $table->boolean('is_hidden')->default(false)->after('message');
            $table->timestamp('edited_at')->nullable()->after('is_hidden');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'is_hidden', 'edited_at']);
        });
    }
};
