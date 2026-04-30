<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->timestamp('last_confirmed_at')->nullable()->after('status');
            $table->timestamp('checkin_sent_at')->nullable()->after('last_confirmed_at');
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn(['last_confirmed_at', 'checkin_sent_at']);
        });
    }
};
