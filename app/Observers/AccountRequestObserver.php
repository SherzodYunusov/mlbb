<?php

namespace App\Observers;

use App\Jobs\SendTelegramMessage;
use App\Models\AccountRequest;

class AccountRequestObserver
{
    public function updated(AccountRequest $req): void
    {
        if (! $req->wasChanged('status')) {
            return;
        }

        $user = $req->user;
        if (! $user) {
            return;
        }

        $text = match ($req->status) {
            'active' =>
                "✅ <b>Arizangiz tasdiqlandi!</b>\n\n"
                . "📋 Ariza #{$req->id} e'lonlar taxtasiga qo'yildi.\n"
                . "Sotuvchilar javob yoza boshlaydilar.",

            'closed' =>
                "❌ <b>Arizangiz rad etildi.</b>\n\n"
                . "📋 Ariza #{$req->id} ko'rib chiqildi va qabul qilinmadi.\n"
                . "Qayta ko'rib, yangi ariza yuborishingiz mumkin.",

            default => null,
        };

        if ($text === null) {
            return;
        }

        SendTelegramMessage::dispatch($user->telegram_id, $text);
    }
}
