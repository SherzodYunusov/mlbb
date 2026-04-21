<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Eski Elite/Epic/Legend/Mythic nomlarini yangi 8 darajali tizimga o'tkazish */
    private array $map = [
        'Elite Collector'           => 'Коллекционер-любитель',
        'Epic Collector'            => 'Младший коллекционер',
        'Legend Collector'          => 'Опытный коллекционер',
        'Legend Honor Collector'    => 'Коллекционер-эксперт',
        'Mythic Collector'          => 'Знаменитый коллекционер',
        'Mythic Honor Collector'    => 'Коллекционер-гуру',
        'Mythic Glory Collector'    => 'Мегаколлекционер',
        'Mythic Immortal Collector' => 'Мировой коллекционер',
    ];

    public function up(): void
    {
        foreach ($this->map as $old => $new) {
            DB::table('accounts')
                ->where('collection_level', $old)
                ->update(['collection_level' => $new]);
        }
    }

    public function down(): void
    {
        foreach ($this->map as $old => $new) {
            DB::table('accounts')
                ->where('collection_level', $new)
                ->update(['collection_level' => $old]);
        }
    }
};
