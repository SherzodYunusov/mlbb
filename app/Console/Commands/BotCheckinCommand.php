<?php

namespace App\Console\Commands;

use App\Jobs\SendTelegramMessage;
use App\Models\Account;
use App\Models\Deal;
use Illuminate\Console\Command;

class BotCheckinCommand extends Command
{
    protected $signature   = 'bot:checkin';
    protected $description = '3 kundan ortiq e\'lonlarga aktuallik tekshiruvi yuborish';

    public function handle(): int
    {
        // Faol savdodagi akkauntlar ID lari (checkin yubormaslik uchun)
        $busyAccountIds = Deal::whereIn('status', ['pending_admin', 'ongoing'])
            ->pluck('account_id')
            ->toArray();

        // ── 1. 24 soatdan ortiq javob bermagan e'lonlarni arxivlash ──
        $expired = Account::where('status', 'active')
            ->whereNotNull('checkin_sent_at')
            ->where('checkin_sent_at', '<', now()->subHours(24))
            ->whereNotIn('id', $busyAccountIds)
            ->with('user')
            ->get();

        foreach ($expired as $account) {
            $account->update(['status' => 'archived']);

            $this->line("📦 Arxivlandi #{$account->id}: {$account->collection_level}");

            if (!$account->user) continue;

            SendTelegramMessage::dispatch(
                $account->user->telegram_id,
                "📦 <b>E'lon avtomatik arxivlandi</b>\n\n"
                . "🎮 <b>{$account->collection_level}</b>\n\n"
                . "24 soat davomida tasdiqlash olmanganligi sababli e'lon bozordan olib tashlandi.\n"
                . "Agar akkaunt hali sotuvda bo'lsa, qaytadan e'lon bering."
            );
        }

        // ── 2. 3 kundan ortiq tasdiqlanmagan faol e'lonlarga xabar yuborish ──
        $stale = Account::where('status', 'active')
            ->whereNull('checkin_sent_at')
            ->whereNotIn('id', $busyAccountIds)
            ->where(function ($q) {
                $q->whereNull('last_confirmed_at')
                  ->orWhere('last_confirmed_at', '<', now()->subDays(3));
            })
            ->with('user')
            ->get();

        foreach ($stale as $account) {
            if (!$account->user) continue;

            $account->update(['checkin_sent_at' => now()]);

            $this->line("📨 Checkin yuborildi #{$account->id}: {$account->collection_level}");

            SendTelegramMessage::dispatch(
                $account->user->telegram_id,
                "🔔 <b>E'lon aktualligini tekshiring</b>\n\n"
                . "🎮 <b>{$account->collection_level}</b> e'loningiz hali ham sotuvdami?\n\n"
                . "Agar boshqa joyda sotib yuborgan bo'lsangiz, iltimos, o'chirib qo'ying.",
                [[
                    ['text' => '✅ Ha, hali ham sotuvda', 'callback_data' => "checkin_yes_{$account->id}"],
                    ['text' => '💰 Sotib yubordim',       'callback_data' => "checkin_sold_{$account->id}"],
                ]]
            );
        }

        $this->info('');
        $this->info("✅ Arxivlandi: {$expired->count()} ta | Checkin yuborildi: {$stale->count()} ta");

        return Command::SUCCESS;
    }
}
