<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramMessage;
use App\Models\Deal;
use Illuminate\Console\Command;

class DealTimeoutCommand extends Command
{
    protected $signature   = 'deal:timeout';
    protected $description = '24 soat javobsiz qolgan bitimlarni avtomatik bekor qilish';

    public function handle(): int
    {
        $expired = Deal::with(['account', 'buyer', 'seller'])
            ->where('status', 'pending_admin')
            ->where('created_at', '<', now()->subHours(24))
            ->get();

        foreach ($expired as $deal) {
            if (!$deal->account || !$deal->buyer || !$deal->seller) {
                $this->warn("⚠️ Deal #{$deal->id}: munosabat topilmadi, o'tkazildi");
                $deal->update(['status' => 'cancelled']);
                continue;
            }

            $deal->update(['status' => 'cancelled']);
            $deal->account->update(['status' => 'active']);

            $price = number_format($deal->account->price, 0, '.', ' ');
            $level = $deal->account->collection_level;

            $this->line("⏰ Timeout: Deal #{$deal->id} — {$level}");

            SendTelegramMessage::dispatch(
                $deal->buyer->telegram_id,
                "⏰ <b>So'rovingiz muddati o'tdi.</b>\n\n"
                . "🎮 {$level}\n"
                . "💰 {$price} so'm\n\n"
                . "Admin 24 soat ichida ko'rib chiqmadi — so'rov avtomatik bekor qilindi.\n"
                . "Xohlasangiz qaytadan urinib ko'ring."
            );

            SendTelegramMessage::dispatch(
                $deal->seller->telegram_id,
                "ℹ️ <b>Bitim muddati o'tdi.</b>\n\n"
                . "🎮 {$level}\n\n"
                . "Akkauntingiz qayta sotuvga qo'yildi. ✅"
            );
        }

        $this->info("✅ Timeout: {$expired->count()} ta bitim bekor qilindi");

        return Command::SUCCESS;
    }
}
