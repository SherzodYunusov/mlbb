<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Deal;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Log;

class BotController extends Controller
{
    public function __construct(private TelegramService $telegram) {}

    // ══════════════════════════════════════════════
    //  MAIN ROUTER
    // ══════════════════════════════════════════════

    public function handleUpdate(array $update): void
    {
        $callback = $update['callback_query'] ?? null;
        if (!$callback) return;

        $data       = trim($callback['data'] ?? '');
        $callbackId = $callback['id']        ?? '';
        $from       = $callback['from']      ?? [];
        $fromId     = (int) ($from['id']     ?? 0);

        Log::info('Callback', ['data' => $data, 'from' => $fromId]);

        match(true) {
            str_starts_with($data, 'approve_')      => $this->approveAccount((int) substr($data, 8), $callbackId),
            str_starts_with($data, 'reject_')       => $this->rejectAccount((int) substr($data, 7), $callbackId),
            str_starts_with($data, 'buy_')          => $this->handleBuyRequest((int) substr($data, 4), $callbackId, $fromId),
            str_starts_with($data, 'open_deal_')    => $this->openDealGroup((int) substr($data, 10), $callbackId),
            str_starts_with($data, 'cancel_deal_')  => $this->cancelDeal((int) substr($data, 12), $callbackId),
            str_starts_with($data, 'finish_deal_')  => $this->finishDeal((int) substr($data, 12), $callbackId, $fromId),
            default => $this->telegram->answerCallbackQuery($callbackId),
        };
    }

    // ══════════════════════════════════════════════
    //  APPROVE / REJECT account
    // ══════════════════════════════════════════════

    private function approveAccount(int $accountId, string $callbackId): void
    {
        $account = Account::with('user')->find($accountId);
        if (!$account) { $this->telegram->answerCallbackQuery($callbackId, '❗ Topilmadi'); return; }
        if ($account->status !== 'pending') { $this->telegram->answerCallbackQuery($callbackId, 'Allaqachon ko\'rib chiqilgan'); return; }

        $account->update(['status' => 'active']);

        // Kanal xabarini yangilaymiz + "Sotib olish" tugmasi qo'shamiz
        if ($account->channel_message_id) {
            $this->telegram->editMessageText(
                $this->telegram->adminChannelId,
                (int) $account->channel_message_id,
                $this->buildAccountText($account, '✅ TASDIQLANDI — Sotuvda'),
                [[['text' => '💰 Sotib olish', 'callback_data' => "buy_{$account->id}"]]]
            );
        }

        $this->telegram->answerCallbackQuery($callbackId, '✅ Tasdiqlandi!');
        $this->notifySeller($account, approved: true);
    }

    private function rejectAccount(int $accountId, string $callbackId): void
    {
        $account = Account::with('user')->find($accountId);
        if (!$account) { $this->telegram->answerCallbackQuery($callbackId, '❗ Topilmadi'); return; }
        if ($account->status !== 'pending') { $this->telegram->answerCallbackQuery($callbackId, 'Allaqachon ko\'rib chiqilgan'); return; }

        $account->update(['status' => 'rejected']);

        if ($account->channel_message_id) {
            $this->telegram->editMessageText(
                $this->telegram->adminChannelId,
                (int) $account->channel_message_id,
                $this->buildAccountText($account, '❌ RAD ETILDI'),
                []
            );
        }

        $this->telegram->answerCallbackQuery($callbackId, '❌ Rad etildi');
        $this->notifySeller($account, approved: false);
    }

    // ══════════════════════════════════════════════
    //  BUY REQUEST
    // ══════════════════════════════════════════════

    private function handleBuyRequest(int $accountId, string $callbackId, int $buyerTgId): void
    {
        $account = Account::with('user')->find($accountId);

        if (!$account || $account->status !== 'active') {
            $this->telegram->answerCallbackQuery($callbackId, '❗ Akkaunt sotuvda emas', true);
            return;
        }

        // O'z akkauntini sotib olishni oldini olamiz
        if ($account->user->telegram_id === $buyerTgId) {
            $this->telegram->answerCallbackQuery($callbackId, '❌ O\'z akkauntingizni sotib ololmaysiz!', true);
            return;
        }

        $buyer = User::where('telegram_id', $buyerTgId)->first();

        if (!$buyer) {
            $this->telegram->answerCallbackQuery($callbackId, '❗ Avval botga /start yuboring', true);
            return;
        }

        // Faol bitim bormi?
        $existingDeal = Deal::where('account_id', $accountId)
            ->whereIn('status', ['pending_admin', 'ongoing'])
            ->exists();

        if ($existingDeal) {
            $this->telegram->answerCallbackQuery($callbackId, '⏳ Bu akkaunt uchun bitim jarayonda', true);
            return;
        }

        // Xaridor 1 soat ichida shu akkaunt bo'yicha bekor qilingan deal qilganmi?
        $recentCancel = Deal::where('account_id', $accountId)
            ->where('buyer_id', $buyer->id)
            ->where('status', 'cancelled')
            ->where('updated_at', '>=', now()->subHour())
            ->exists();

        if ($recentCancel) {
            $this->telegram->answerCallbackQuery($callbackId, '⏳ Siz bu akkaunt bo\'yicha qayta so\'rov yuborishingiz uchun 1 soat kutishingiz kerak', true);
            return;
        }

        // Deal yaratamiz
        $deal = Deal::create([
            'account_id' => $account->id,
            'buyer_id'   => $buyer->id,
            'seller_id'  => $account->user->id,
            'status'     => 'pending_admin',
        ]);

        // Admin ga xabar yuboramiz
        $buyerName   = $buyer->username ? "@{$buyer->username}" : $buyer->first_name;
        $sellerName  = $account->user->username ? "@{$account->user->username}" : $account->user->first_name;
        $price       = number_format($account->price, 0, '.', ' ');

        $adminText = "🔔 <b>Yangi sotib olish so'rovi!</b>\n\n"
                   . "💰 <b>Akkaunt #{$account->id}</b>\n"
                   . "📦 Narx: <b>{$price} so'm</b>\n"
                   . "🏆 {$account->collection_level}\n\n"
                   . "🛒 <b>Xaridor:</b> {$buyerName}\n"
                   . "👤 <b>Sotuvchi:</b> {$sellerName}\n\n"
                   . "Bitim guruhini ochasizmi?";

        $keyboard = [
            [
                ['text' => '🤝 Guruh yaratish', 'callback_data' => "open_deal_{$deal->id}"],
                ['text' => '❌ Rad etish',       'callback_data' => "cancel_deal_{$deal->id}"],
            ],
        ];

        $adminMsgId = $this->telegram->sendMessage($this->telegram->adminId, $adminText, $keyboard);

        $deal->update(['admin_message_id' => $adminMsgId]);

        // Xaridorga tasdiqlash kutilmoqda xabari
        $this->telegram->answerCallbackQuery($callbackId, '✅ So\'rovingiz adminga yuborildi!', true);
        $this->telegram->sendMessage(
            $buyerTgId,
            "⏳ <b>So'rovingiz yuborildi!</b>\n\n"
            . "Admin tez orada ko'rib chiqadi.\n"
            . "Akkaunt: <b>#{$account->id}</b> — {$price} so'm"
        );
    }

    // ══════════════════════════════════════════════
    //  OPEN DEAL GROUP
    // ══════════════════════════════════════════════

    private function openDealGroup(int $dealId, string $callbackId): void
    {
        $deal = Deal::with(['account.user', 'buyer', 'seller'])->find($dealId);

        if (!$deal || $deal->status !== 'pending_admin') {
            $this->telegram->answerCallbackQuery($callbackId, '❗ Bitim topilmadi yoki allaqachon qayta ishlangan');
            return;
        }

        $account    = $deal->account;
        $buyer      = $deal->buyer;
        $seller     = $deal->seller;
        $price      = number_format($account->price, 0, '.', ' ');
        $buyerName  = $buyer->username  ? "@{$buyer->username}"  : $buyer->first_name;
        $sellerName = $seller->username ? "@{$seller->username}" : $seller->first_name;

        $groupChatId = $this->telegram->dealsGroupId;
        $topicId     = null;

        // ── Forum topic yaratish (agar deals guruh sozlangan bo'lsa) ──
        if ($groupChatId) {
            $topicName = "Deal #{$deal->id} — {$account->collection_level}";
            $topicId   = $this->telegram->createForumTopic($groupChatId, $topicName);

            // Admin ni to'liq admin qilamiz
            $this->telegram->promoteChatMember($groupChatId, $this->telegram->adminId);

            // Xaridor va sotuvchiga invite link yuboramiz
            $buyerLink  = $this->telegram->createInviteLink($groupChatId, "Xaridor #{$deal->id}");
            $sellerLink = $this->telegram->createInviteLink($groupChatId, "Sotuvchi #{$deal->id}");

            if ($buyerLink) {
                $this->telegram->sendMessage(
                    $buyer->telegram_id,
                    "🤝 <b>Bitim guruhiga taklif!</b>\n\n"
                    . "Deal <b>#{$deal->id}</b> uchun guruhga qo'shiling:\n{$buyerLink}\n\n"
                    . "⚠️ Link 24 soat davomida amal qiladi."
                );
            } else {
                $this->notifyAdminInviteFailed($buyer, $deal->id, 'xaridor');
            }

            if ($sellerLink) {
                $this->telegram->sendMessage(
                    $seller->telegram_id,
                    "🤝 <b>Bitim guruhiga taklif!</b>\n\n"
                    . "Deal <b>#{$deal->id}</b> uchun guruhga qo'shiling:\n{$sellerLink}\n\n"
                    . "⚠️ Link 24 soat davomida amal qiladi."
                );
            } else {
                $this->notifyAdminInviteFailed($seller, $deal->id, 'sotuvchi');
            }

            // Guruhda xush kelibsiz xabari
            $welcomeText = $this->buildWelcomeText($deal, $buyerName, $sellerName, $price);
            $finishBtn   = [[['text' => '✅ Bitimni yakunlash (Admin)', 'callback_data' => "finish_deal_{$deal->id}"]]];
            $this->telegram->sendMessage($groupChatId, $welcomeText, $finishBtn, $topicId);

        } else {
            // Deals guruh sozlanmagan — xaridorga private xabar
            $this->telegram->sendMessage(
                $buyer->telegram_id,
                "🤝 <b>Bitim boshlandi!</b>\n\n"
                . "Akkaunt: <b>#{$account->id}</b>\n"
                . "Narx: <b>{$price} so'm</b>\n"
                . "Sotuvchi: {$sellerName}\n\n"
                . "Admin tez orada siz bilan bog'lanadi."
            );
            $this->telegram->sendMessage(
                $seller->telegram_id,
                "🤝 <b>Bitim boshlandi!</b>\n\n"
                . "Akkaunt: <b>#{$account->id}</b>\n"
                . "Narx: <b>{$price} so'm</b>\n"
                . "Xaridor: {$buyerName}\n\n"
                . "Admin tez orada siz bilan bog'lanadi."
            );
        }

        // Deal statusini yangilaymiz
        $deal->update([
            'status'       => 'ongoing',
            'group_chat_id' => $groupChatId,
            'topic_id'     => $topicId,
        ]);

        // Admin xabarini yangilaymiz
        if ($deal->admin_message_id) {
            $this->telegram->editMessageText(
                $this->telegram->adminId,
                (int) $deal->admin_message_id,
                "✅ <b>Bitim #{$deal->id} ochildi</b>\n\n"
                . "Xaridor: {$buyerName}\nSotuvchi: {$sellerName}\n"
                . "Akkaunt: #{$account->id} — {$price} so'm",
                [[['text' => '✅ Bitimni yakunlash', 'callback_data' => "finish_deal_{$deal->id}"]]]
            );
        }

        $this->telegram->answerCallbackQuery($callbackId, '🤝 Bitim guruh ochildi!');
    }

    // ══════════════════════════════════════════════
    //  CANCEL DEAL
    // ══════════════════════════════════════════════

    private function cancelDeal(int $dealId, string $callbackId): void
    {
        $deal = Deal::with(['buyer', 'seller', 'account'])->find($dealId);

        if (!$deal || $deal->status !== 'pending_admin') {
            $this->telegram->answerCallbackQuery($callbackId, '❗ Bitim topilmadi');
            return;
        }

        $deal->update(['status' => 'cancelled']);

        // Admin xabarini yangilaymiz
        if ($deal->admin_message_id) {
            $this->telegram->editMessageText(
                $this->telegram->adminId,
                (int) $deal->admin_message_id,
                "❌ <b>Bitim #{$deal->id} rad etildi</b>\n\nAkkaunt: #{$deal->account_id}",
                []
            );
        }

        // Xaridorga xabar
        $this->telegram->sendMessage(
            $deal->buyer->telegram_id,
            "😔 <b>Bitim rad etildi</b>\n\n"
            . "Akkaunt <b>#{$deal->account_id}</b> bo'yicha so'rovingiz admin tomonidan rad etildi."
        );

        $this->telegram->answerCallbackQuery($callbackId, '❌ Bitim rad etildi');
    }

    // ══════════════════════════════════════════════
    //  FINISH DEAL
    // ══════════════════════════════════════════════

    private function finishDeal(int $dealId, string $callbackId, int $fromTgId): void
    {
        // Faqat admin yakunlashi mumkin
        if ($fromTgId !== $this->telegram->adminId) {
            $this->telegram->answerCallbackQuery($callbackId, '⛔ Faqat admin yakunlay oladi', true);
            return;
        }

        $deal = Deal::with(['buyer', 'seller', 'account'])->find($dealId);

        if (!$deal || $deal->status !== 'ongoing') {
            $this->telegram->answerCallbackQuery($callbackId, '❗ Bitim topilmadi yoki allaqachon yakunlangan');
            return;
        }

        $deal->update(['status' => 'completed']);
        $deal->account->update(['status' => 'sold']);

        $price = number_format($deal->account->price, 0, '.', ' ');

        // Guruhda yakunlash xabari
        if ($deal->group_chat_id) {
            $this->telegram->sendMessage(
                $deal->group_chat_id,
                "🎉 <b>Bitim muvaffaqiyatli yakunlandi!</b>\n\n"
                . "Akkaunt: <b>#{$deal->account_id}</b>\n"
                . "Narx: <b>{$price} so'm</b>\n\n"
                . "Rahmat! ✅",
                [],
                $deal->topic_id
            );
        }

        // Xaridorga
        $this->telegram->sendMessage(
            $deal->buyer->telegram_id,
            "🎉 <b>Bitim yakunlandi!</b>\n\n"
            . "Akkaunt <b>#{$deal->account_id}</b> muvaffaqiyatli sotib olindi.\n"
            . "Narx: <b>{$price} so'm</b>\n\n"
            . "Xaridingiz uchun rahmat! ✅"
        );

        // Sotuvchiga
        $this->telegram->sendMessage(
            $deal->seller->telegram_id,
            "🎉 <b>Bitim yakunlandi!</b>\n\n"
            . "Akkaunt <b>#{$deal->account_id}</b> sotildi.\n"
            . "Narx: <b>{$price} so'm</b>\n\n"
            . "Muvaffaqiyatli savdo! ✅"
        );

        // Admin xabarini yangilaymiz
        if ($deal->admin_message_id) {
            $this->telegram->editMessageText(
                $this->telegram->adminId,
                (int) $deal->admin_message_id,
                "✅ <b>Bitim #{$deal->id} yakunlandi</b>\n\nAkkaunt #{$deal->account_id} — {$price} so'm",
                []
            );
        }

        $this->telegram->answerCallbackQuery($callbackId, '🎉 Bitim yakunlandi!');
    }

    // ══════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════

    private function notifySeller(Account $account, bool $approved): void
    {
        $text = $approved
            ? "🎉 <b>Akkauntingiz tasdiqlandi!</b>\n\n"
              . "Akkaunt <b>#{$account->id}</b> endi sotuvda ko'rinadi.\n"
              . "💰 Narx: <b>" . number_format($account->price, 0, '.', ' ') . " so'm</b>"
            : "😔 <b>Akkauntingiz rad etildi</b>\n\n"
              . "Akkaunt <b>#{$account->id}</b> qoidalarga mos kelmadi.\n"
              . "To'g'rilab qaytadan yuborishingiz mumkin.";

        $this->telegram->sendMessage($account->user->telegram_id, $text);
    }

    private function notifyAdminInviteFailed(User $user, int $dealId, string $role): void
    {
        $name = $user->username ? "@{$user->username}" : $user->first_name;
        $this->telegram->sendMessage(
            $this->telegram->adminId,
            "⚠️ <b>Invite yuborib bo'lmadi</b>\n\n"
            . "Deal #{$dealId} — {$role}: {$name}\n"
            . "Privacy sozlamalari tufayli link yaratilmadi.\n"
            . "Iltimos, qo'lda taklif qiling."
        );
    }

    private function buildAccountText(Account $account, string $statusLine): string
    {
        $seller   = $account->user->username ? "@{$account->user->username}" : $account->user->first_name;
        $transfer = $account->ready_for_transfer ? '✅ Ha' : '❌ Yo\'q';
        $price    = number_format($account->price, 0, '.', ' ');

        $lines = [
            "<b>{$statusLine}</b>", '',
            "💰 <b>Narx:</b> {$price} so'm",
            "⚔️ <b>Qahramonlar:</b> {$account->heroes_count} ta",
            "👗 <b>Skinlar:</b> {$account->skins_count} ta",
            "🏆 <b>Kolleksiya:</b> {$account->collection_level}",
            "🔄 <b>Transfer:</b> {$transfer}",
        ];

        if (!empty($account->description)) {
            $lines[] = '';
            $lines[] = "📝 {$account->description}";
        }

        $lines[] = '';
        $lines[] = "👤 <b>Sotuvchi:</b> {$seller}";
        $lines[] = "🆔 <b>ID:</b> #{$account->id}";

        return implode("\n", $lines);
    }

    private function buildWelcomeText(Deal $deal, string $buyerName, string $sellerName, string $price): string
    {
        return "🤝 <b>Bitim #{$deal->id} — Garant xizmati</b>\n\n"
             . "📦 <b>Akkaunt:</b> #{$deal->account_id}\n"
             . "🏆 {$deal->account->collection_level}\n"
             . "💰 <b>Narx:</b> {$price} so'm\n\n"
             . "🛒 <b>Xaridor:</b> {$buyerName}\n"
             . "👤 <b>Sotuvchi:</b> {$sellerName}\n\n"
             . "ℹ️ Admin bitimni kuzatib boradi.\n"
             . "Muammo bo'lsa adminga murojaat qiling.\n\n"
             . "✅ Bitim yakunlangach admin tasdiqlaydi.";
    }
}
