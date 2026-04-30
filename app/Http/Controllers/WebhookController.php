<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Jobs\SendTelegramMessage;
use App\Models\AccountRequest;
use App\Models\Comment;
use App\Models\Deal;
use App\Models\LoginToken;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public function __construct(private TelegramService $telegram) {}

    public function handle(Request $request): Response
    {
        try {
            $this->handleUpdate($request->all());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        // Har doim 200 — Telegram qayta yubormaydi
        return response('ok', 200);
    }

    public function handleUpdate(array $update): void
    {
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        if (isset($update['callback_query'])) {
            $this->handleCallback($update['callback_query']);
        }
    }

    // ──────────────────────────────────────────────
    //  MESSAGE
    // ──────────────────────────────────────────────

    private function handleMessage(array $message): void
    {
        $chat = $message['chat'];
        $from = $message['from'];
        $text = $message['text'] ?? '';

        $user = User::updateOrCreate(
            ['telegram_id' => $from['id']],
            [
                'username'   => $from['username']   ?? null,
                'first_name' => $from['first_name'] ?? 'Foydalanuvchi',
            ]
        );

        // /start, /start@botname, /start acc_12
        if ($text === '/start' || str_starts_with($text, '/start@') || str_starts_with($text, '/start ')) {
            $param = str_starts_with($text, '/start ') ? trim(substr($text, 7)) : null;
            $this->sendStart($chat['id'], $user, $param);
            return;
        }

        // Faol bitim relay — xaridor ↔ sotuvchi xabarlari bot orqali
        $deal = Deal::whereIn('status', ['ongoing', 'pending_admin'])
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)->orWhere('seller_id', $user->id);
            })
            ->with(['buyer', 'seller', 'account'])
            ->first();

        if ($deal && $deal->status === 'pending_admin') {
            $level = $deal->account->collection_level ?? '';
            $this->telegram->sendMessage(
                $chat['id'],
                "⏳ <b>So'rovingiz ko'rib chiqilmoqda.</b>\n\n"
                . ($level ? "🎮 {$level}\n\n" : '')
                . "Admin tez orada javob beradi. Biroz kuting."
            );
            return;
        }

        if ($deal && $deal->status === 'ongoing') {
            $isBuyer = $deal->buyer_id === $user->id;
            $role    = $isBuyer ? '🛒 Xaridor' : '👤 Sotuvchi';
            $other   = $isBuyer ? $deal->seller : $deal->buyer;

            if ($text) {
                $this->telegram->sendMessage(
                    $other->telegram_id,
                    "💬 <b>{$role}:</b>\n" . htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
                );
            } else {
                // Rasm, fayl, ovoz — copyMessage bilan relay
                $this->telegram->sendMessage($other->telegram_id, "💬 <b>{$role}:</b> 👇");
                $this->telegram->copyMessage($other->telegram_id, $chat['id'], $message['message_id']);
            }
            return;
        }

        // Boshqa har qanday xabar → yo'riqnoma
        if ($text) {
            $this->sendHelp($chat['id'], $user);
        }
    }

    private function sendHelp(int|string $chatId, User $user): void
    {
        $token      = $this->makeToken($user);
        $marketUrl  = config('app.url') . '/webapp?token=' . $token;
        $profileUrl = config('app.url') . '/profile/' . $user->telegram_id;

        $this->telegram->sendMessage(
            $chatId,
            "ℹ️ <b>MLBB Market buyruqlari:</b>\n\n"
            . "/start — Bosh menyu\n\n"
            . "Tugmalar orqali boshqaring 👇",
            [
                [['text' => '🛒 Marketplace',   'url' => $marketUrl]],
                [['text' => '👤 Mening profilim', 'url' => $profileUrl]],
                [['text' => '📋 Mening e\'lonlarim', 'callback_data' => 'my_accounts']],
            ]
        );
    }

    private function sendStart(int|string $chatId, User $user, ?string $param = null): void
    {
        $token  = $this->makeToken($user);
        $webUrl = config('app.url') . '/webapp?token=' . $token;

        // Deep link: ?start=acc_12 → to'g'ridan akkauntni ochish
        if ($param && str_starts_with($param, 'acc_')) {
            $accountId = (int) substr($param, 4);
            $account   = Account::find($accountId);

            if ($account && $account->status === 'active') {
                $webUrl .= '&account=' . $accountId;

                $this->telegram->sendMessage(
                    $chatId,
                    "🎮 <b>Akkaunt topildi!</b>\n\n"
                    . "🏆 {$account->collection_level}\n"
                    . "💰 " . number_format($account->price, 0, '.', ' ') . " so'm\n"
                    . "⚔️ {$account->heroes_count} ta qahramon | 👗 {$account->skins_count} ta skin\n\n"
                    . "Ko'rish uchun quyidagi tugmani bosing:",
                    [[['text' => "🛒 Akkauntni ko'rish", 'url' => $webUrl]]]
                );
                return;
            }
        }

        $profileUrl = config('app.url') . '/profile/' . $user->telegram_id;

        $text = "👋 Salom, <b>{$user->first_name}</b>!\n\n"
              . "🎮 <b>MLBB Market</b> ga xush kelibsiz!\n\n"
              . "Bu yerda siz:\n"
              . "• Akkauntlarni sotib olishingiz\n"
              . "• Akkauntingizni sotishga qo'yishingiz mumkin\n\n"
              . "⬇️ Quyidagi tugmalardan foydalaning:";

        $requestsUrl = config('app.url') . '/webapp/requests?token=' . $token;

        $keyboard = [
            [['text' => '🛒 MLBB Marketplace',     'url' => $webUrl]],
            [['text' => '👤 Mening profilim',       'url' => $profileUrl]],
            [['text' => '📋 Mening e\'lonlarim',    'callback_data' => 'my_accounts']],
            [['text' => '🔍 Akkaunt qidiruv',       'url' => $requestsUrl]],
        ];

        $this->telegram->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    //  CALLBACK
    // ──────────────────────────────────────────────

    private function handleCallback(array $callback): void
    {
        // Eski xabarlarda 'message' bo'lmasligi mumkin
        if (!isset($callback['message'])) {
            $this->telegram->answerCallbackQuery($callback['id'] ?? '');
            return;
        }

        $chatId      = $callback['message']['chat']['id'];
        $messageId   = $callback['message']['message_id'];
        $from        = $callback['from'];
        $data        = $callback['data'] ?? '';
        $callbackId  = $callback['id'];
        $isAdmin     = $from['id'] === $this->telegram->adminId;

        // buy_, buyer_cancel_, seller_cancel_, hide_comment_ o'z answerCallbackQuery ni chaqiradi
        // (show_alert ishlatadi) — ular uchun bu yerda javob bermaslik kerak
        $selfAnswered = ['buy_', 'buyer_cancel_', 'seller_cancel_', 'hide_comment_'];
        $hasSelfAnswer = false;
        foreach ($selfAnswered as $prefix) {
            if (str_starts_with($data, $prefix)) { $hasSelfAnswer = true; break; }
        }
        if (!$hasSelfAnswer) {
            $this->telegram->answerCallbackQuery($callbackId);
        }

        \Illuminate\Support\Facades\Log::info('CB', compact('data', 'isAdmin'));

        // ── Foydalanuvchi: mening akkauntlarim ──
        if ($data === 'my_accounts') {
            $user = User::where('telegram_id', $from['id'])->first();
            if (!$user) {
                $this->telegram->sendMessage($chatId, "Avval /start yuboring.");
                return;
            }
            $token = $this->makeToken($user);
            $url   = config('app.url') . '/webapp?token=' . $token . '&tab=profile';
            $this->telegram->sendMessage(
                $chatId,
                "📋 <b>Mening akkauntlarim</b>\n\nQuyidagi tugmani bosing:",
                [[['text' => "📋 Akkauntlarimni ko'rish", 'url' => $url]]]
            );
            return;
        }

        // ── Admin: so'rovni tasdiqlash (approve_req_ — approve_ dan OLDIN) ──
        if (str_starts_with($data, 'approve_req_')) {
            if (!$isAdmin) return;
            $reqId = (int) substr($data, 12);
            $this->approveRequest($reqId, $chatId, $messageId);
            return;
        }

        // ── Admin: so'rovni rad etish (reject_req_ — reject_ dan OLDIN) ──
        if (str_starts_with($data, 'reject_req_')) {
            if (!$isAdmin) return;
            $reqId = (int) substr($data, 11);
            $this->rejectRequest($reqId, $chatId, $messageId);
            return;
        }

        // ── Admin: akkauntni tasdiqlash ──
        if (str_starts_with($data, 'approve_')) {
            if (!$isAdmin) return;
            $accountId = (int) substr($data, 8);
            $this->approveAccount($accountId, $chatId, $messageId);
            return;
        }

        // ── Admin: akkauntni rad etish ──
        if (str_starts_with($data, 'reject_')) {
            if (!$isAdmin) return;
            $accountId = (int) substr($data, 7);
            $this->rejectAccount($accountId, $chatId, $messageId);
            return;
        }

        // ── Admin: bitim guruhini ochish ──
        if (str_starts_with($data, 'open_deal_')) {
            if (!$isAdmin) return;
            $dealId = (int) substr($data, 10);
            $this->openDeal($dealId, $chatId, $messageId);
            return;
        }

        // ── Admin: bitimni yakunlash ──
        if (str_starts_with($data, 'complete_deal_')) {
            if (!$isAdmin) return;
            $dealId = (int) substr($data, 14);
            $this->completeDeal($dealId, $chatId, $messageId);
            return;
        }

        // ── Admin: bitimni bekor qilish ──
        if (str_starts_with($data, 'cancel_deal_')) {
            if (!$isAdmin) return;
            $dealId = (int) substr($data, 12);
            $this->cancelDeal($dealId, $chatId, $messageId);
            return;
        }

        // ── finish_deal_ = complete_deal_ ning sinonimidir (BotController dan kelgan eski tugmalar) ──
        if (str_starts_with($data, 'finish_deal_')) {
            if (!$isAdmin) return;
            $dealId = (int) substr($data, 12);
            $this->completeDeal($dealId, $chatId, $messageId);
            return;
        }

        // ── Xaridor: pending_admin da o'zi bekor qiladi ──
        if (str_starts_with($data, 'buyer_cancel_')) {
            $dealId = (int) substr($data, 13);
            $this->buyerCancelDeal($dealId, $from['id'], $callbackId, $chatId, $messageId);
            return;
        }

        // ── Sotuvchi: ongoing da bitimdan chiqadi ──
        if (str_starts_with($data, 'seller_cancel_')) {
            $dealId = (int) substr($data, 14);
            $this->sellerCancelDeal($dealId, $from['id'], $callbackId, $chatId, $messageId);
            return;
        }

        // ── Sotib olish tugmasi (kanaldan) ──
        if (str_starts_with($data, 'buy_')) {
            $accountId = (int) substr($data, 4);
            $this->handleBuyRequest($accountId, $callbackId, $from['id']);
            return;
        }

        // ── Admin: izohni yashirish ──
        if (str_starts_with($data, 'hide_comment_')) {
            if (!$isAdmin) return;
            $commentId = (int) substr($data, 13);
            $comment   = Comment::find($commentId);
            if ($comment) {
                $comment->update(['is_hidden' => true]);
                $this->telegram->editMessageReplyMarkup($chatId, $messageId, []);
                $this->telegram->answerCallbackQuery($callbackId, '🚫 Izoh yashirildi', true);
            }
            return;
        }

        // ── Checkin: hali ham sotuvda ──
        if (str_starts_with($data, 'checkin_yes_')) {
            $accountId = (int) substr($data, 12);
            $this->checkinConfirm($accountId, $from['id'], $chatId, $messageId);
            return;
        }

        // ── Checkin: sotib yubordim → arxivlash ──
        if (str_starts_with($data, 'checkin_sold_')) {
            $accountId = (int) substr($data, 13);
            $this->checkinArchive($accountId, $from['id'], $chatId, $messageId);
            return;
        }

    }

    // ──────────────────────────────────────────────
    //  ACCOUNT: approve / reject
    // ──────────────────────────────────────────────

    private function approveAccount(int $accountId, int|string $adminChatId, int $messageId): void
    {
        $account = Account::with('user')->find($accountId);

        if (!$account) {
            $this->telegram->editMessageText($adminChatId, $messageId, "❌ Akkaunt topilmadi (#$accountId)");
            return;
        }

        if ($account->status === 'active') {
            $this->telegram->editMessageText(
                $adminChatId, $messageId,
                "✅ Bu akkaunt allaqachon tasdiqlangan! (#{$accountId})"
            );
            return;
        }

        $account->update(['status' => 'active', 'last_confirmed_at' => now()]);

        // Kanal xabarini "Sotib olish" tugmasi bilan yangilash
        if ($account->channel_message_id) {
            $this->telegram->editMessageCaption(
                $this->telegram->adminChannelId,
                (int) $account->channel_message_id,
                $this->buildAccountText($account, '✅ TASDIQLANDI — Sotuvda'),
                [[['text' => '💰 Sotib olish', 'callback_data' => "buy_{$account->id}"]]]
            );
        }

        // Admin xabarini har doim yangilash (tugmalarni olib tashlash)
        $this->telegram->editMessageText(
            $adminChatId, $messageId,
            "✅ <b>Tasdiqlandi!</b> Akkaunt #{$accountId} sotuvga qo'yildi.",
            []
        );

        $seller = $account->user;
        if (!$seller) return;

        $token = $this->makeToken($seller);
        $url   = config('app.url') . '/webapp?token=' . $token . '&tab=profile';

        SendTelegramMessage::dispatch(
            $seller->telegram_id,
            "✅ <b>Akkauntingiz tasdiqlandi!</b>\n\n"
            . "🏆 {$account->collection_level}\n"
            . "💰 " . number_format($account->price, 0, '.', ' ') . " so'm\n\n"
            . "Akkauntingiz endi marketplace da ko'rinmoqda.",
            [[['text' => "🛒 Marketplace'ga o'tish", 'url' => $url]]]
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

    private function buyerCancelDeal(int $dealId, int $fromTgId, string $callbackId, int|string $chatId, int $messageId): void
    {
        $deal = Deal::with(['account', 'buyer', 'seller'])->find($dealId);

        if (!$deal || $deal->status !== 'pending_admin') {
            $this->telegram->answerCallbackQuery($callbackId, '❗ Bekor qilish mumkin emas', true);
            return;
        }

        if ($deal->buyer->telegram_id !== $fromTgId) {
            $this->telegram->answerCallbackQuery($callbackId, '⛔ Bu sizning so\'rovingiz emas', true);
            return;
        }

        $deal->update(['status' => 'cancelled']);
        $deal->account->update(['status' => 'active']);

        $price = number_format($deal->account->price, 0, '.', ' ');
        $level = $deal->account->collection_level;

        $this->telegram->editMessageText(
            $chatId, $messageId,
            "❌ <b>So'rovingiz bekor qilindi.</b>\n\n"
            . "🎮 {$level}\n"
            . "💰 {$price} so'm",
            []
        );

        // Adminga xabar
        if ($deal->admin_message_id) {
            $this->telegram->editMessageText(
                $this->telegram->adminId,
                (int) $deal->admin_message_id,
                "❌ <b>Xaridor so'rovni bekor qildi.</b>\n\n"
                . "🎮 {$level} — #{$deal->account_id}\n"
                . "Akkaunt qayta sotuvga qo'yildi.",
                []
            );
        }

        SendTelegramMessage::dispatch(
            $deal->seller->telegram_id,
            "ℹ️ <b>Xaridor bitimdan chiqdi.</b>\n\n"
            . "🎮 {$level}\n\n"
            . "Akkauntingiz qayta sotuvga qo'yildi. ✅"
        );

        $this->telegram->answerCallbackQuery($callbackId, '✅ Bekor qilindi');
    }

    private function sellerCancelDeal(int $dealId, int $fromTgId, string $callbackId, int|string $chatId, int $messageId): void
    {
        $deal = Deal::with(['account', 'buyer', 'seller'])->find($dealId);

        if (!$deal || !in_array($deal->status, ['pending_admin', 'ongoing'])) {
            $this->telegram->answerCallbackQuery($callbackId, '❗ Bitimdan chiqish mumkin emas', true);
            return;
        }

        if ($deal->seller->telegram_id !== $fromTgId) {
            $this->telegram->answerCallbackQuery($callbackId, '⛔ Bu sizning akkauntingiz emas', true);
            return;
        }

        $deal->update(['status' => 'cancelled']);
        $deal->account->update(['status' => 'active']);

        $price = number_format($deal->account->price, 0, '.', ' ');
        $level = $deal->account->collection_level;

        $this->telegram->editMessageText(
            $chatId, $messageId,
            "❌ <b>Bitimdan chiqdingiz.</b>\n\n"
            . "🎮 {$level}\n"
            . "💰 {$price} so'm\n\n"
            . "Akkauntingiz qayta sotuvga qo'yildi.",
            []
        );

        // Adminga xabar
        if ($deal->admin_message_id) {
            $this->telegram->editMessageText(
                $this->telegram->adminId,
                (int) $deal->admin_message_id,
                "⚠️ <b>Sotuvchi bitimdan chiqdi!</b>\n\n"
                . "🎮 {$level} — #{$deal->account_id}\n"
                . "Akkaunt qayta sotuvga qo'yildi.",
                []
            );
        }

        SendTelegramMessage::dispatch(
            $deal->buyer->telegram_id,
            "😔 <b>Sotuvchi bitimdan chiqdi.</b>\n\n"
            . "🎮 {$level}\n"
            . "💰 {$price} so'm\n\n"
            . "Boshqa akkauntlarni ko'rish uchun marketplace ga kiring."
        );

        $this->telegram->answerCallbackQuery($callbackId, '✅ Bitimdan chiqdingiz');
    }

    private function handleBuyRequest(int $accountId, string $callbackId, int $buyerTgId): void
    {
        $account = Account::with('user')->find($accountId);

        if (!$account || $account->status !== 'active') {
            $this->telegram->answerCallbackQuery($callbackId, '❗ Akkaunt sotuvda emas', true);
            return;
        }

        if ($account->user->telegram_id === $buyerTgId) {
            $this->telegram->answerCallbackQuery($callbackId, '❌ O\'z akkauntingizni sotib ololmaysiz!', true);
            return;
        }

        $buyer = User::where('telegram_id', $buyerTgId)->first();
        if (!$buyer) {
            $this->telegram->answerCallbackQuery($callbackId, '❗ Avval botga /start yuboring', true);
            return;
        }

        $existingDeal = Deal::where('account_id', $accountId)
            ->whereIn('status', ['pending_admin', 'ongoing'])
            ->exists();

        if ($existingDeal) {
            $this->telegram->answerCallbackQuery($callbackId, '⏳ Bu akkaunt uchun bitim jarayonda', true);
            return;
        }

        $recentCancel = Deal::where('account_id', $accountId)
            ->where('buyer_id', $buyer->id)
            ->where('status', 'cancelled')
            ->where('updated_at', '>=', now()->subHour())
            ->exists();

        if ($recentCancel) {
            $this->telegram->answerCallbackQuery($callbackId, '⏳ 1 soat kuting — avvalgi so\'rovingiz bekor qilindi', true);
            return;
        }

        $deal = Deal::create([
            'account_id' => $account->id,
            'buyer_id'   => $buyer->id,
            'seller_id'  => $account->user->id,
            'status'     => 'pending_admin',
        ]);

        $buyerName  = $buyer->username ? "@{$buyer->username}" : $buyer->first_name;
        $sellerName = $account->user->username ? "@{$account->user->username}" : $account->user->first_name;
        $price      = number_format($account->price, 0, '.', ' ');

        $adminText = "🔔 <b>Yangi sotib olish so'rovi!</b>\n\n"
                   . "💰 <b>Akkaunt #{$account->id}</b>\n"
                   . "📦 Narx: <b>{$price} so'm</b>\n"
                   . "🏆 {$account->collection_level}\n\n"
                   . "🛒 <b>Xaridor:</b> {$buyerName}\n"
                   . "👤 <b>Sotuvchi:</b> {$sellerName}\n\n"
                   . "Bitim guruhini ochasizmi?";

        $adminMsgId = $this->telegram->sendMessage(
            $this->telegram->adminId,
            $adminText,
            [[
                ['text' => '🤝 Guruh yaratish', 'callback_data' => "open_deal_{$deal->id}"],
                ['text' => '❌ Rad etish',       'callback_data' => "cancel_deal_{$deal->id}"],
            ]]
        );

        $deal->update(['admin_message_id' => $adminMsgId]);

        $this->telegram->answerCallbackQuery($callbackId, '✅ So\'rovingiz adminga yuborildi!', true);

        SendTelegramMessage::dispatch(
            $buyerTgId,
            "⏳ <b>So'rovingiz yuborildi!</b>\n\n"
            . "Admin tez orada ko'rib chiqadi.\n"
            . "Akkaunt: <b>#{$account->id}</b> — {$price} so'm\n\n"
            . "Fikridan qaytgan bo'lsangiz, quyidagi tugmani bosing:",
            [[['text' => '❌ Bekor qilish', 'callback_data' => "buyer_cancel_{$deal->id}"]]]
        );
    }

    private function rejectAccount(int $accountId, int|string $adminChatId, int $messageId): void
    {
        $account = Account::with('user')->find($accountId);

        if (!$account) {
            $this->telegram->editMessageText($adminChatId, $messageId, "❌ Akkaunt topilmadi (#$accountId)");
            return;
        }

        if ($account->status === 'rejected') {
            $this->telegram->editMessageText(
                $adminChatId, $messageId,
                "❌ Bu akkaunt allaqachon rad etilgan! (#{$accountId})"
            );
            return;
        }

        $account->update(['status' => 'rejected']);

        // Admin xabarini yangilash
        $this->telegram->editMessageText(
            $adminChatId, $messageId,
            "❌ <b>Rad etildi.</b> Akkaunt #{$accountId}.",
            []
        );

        if (!$account->user) return;

        SendTelegramMessage::dispatch(
            $account->user->telegram_id,
            "❌ <b>Akkauntingiz rad etildi.</b>\n\n"
            . "🏆 {$account->collection_level}\n\n"
            . "Sabab haqida ko'proq ma'lumot olish uchun admin bilan bog'laning."
        );
    }

    // ──────────────────────────────────────────────
    //  DEAL: open / cancel
    // ──────────────────────────────────────────────

    private function openDeal(int $dealId, int|string $adminChatId, int $messageId): void
    {
        $deal = Deal::with(['account', 'buyer', 'seller'])->find($dealId);

        if (!$deal) {
            $this->telegram->editMessageText($adminChatId, $messageId, "❌ Bitim topilmadi (#$dealId)");
            return;
        }

        if ($deal->status !== 'pending_admin') {
            $this->telegram->editMessageText($adminChatId, $messageId, "ℹ️ Bitim allaqachon boshqa holatda: {$deal->status}");
            return;
        }

        $deal->update(['status' => 'ongoing']);

        $buyerName  = $deal->buyer->username  ? "@{$deal->buyer->username}"  : $deal->buyer->first_name;
        $sellerName = $deal->seller->username ? "@{$deal->seller->username}" : $deal->seller->first_name;
        $price      = number_format($deal->account->price, 0, '.', ' ');
        $level      = $deal->account->collection_level;

        // Xaridorga va sotuvchiga xabarlar queue orqali — biri sekin bo'lsa boshqasini to'sib qolmaydi
        SendTelegramMessage::dispatch(
            $deal->buyer->telegram_id,
            "🛒 <b>Sotib olish tasdiqlandi!</b>\n\n"
            . "🎮 <b>{$level}</b>\n"
            . "💰 {$price} so'm\n\n"
            . "✉️ Endi <b>shu botga</b> xabar yozing — sotuvchiga yetkaziladi.\n"
            . "Akkaunt ma'lumotlarini so'rang, to'lovni amalga oshiring.\n\n"
            . "⚠️ <b>To'lovni faqat admin tasdiqlashidan keyin o'tkazing!</b>"
        );

        SendTelegramMessage::dispatch(
            $deal->seller->telegram_id,
            "💰 <b>Akkauntingizni sotib olmoqchi!</b>\n\n"
            . "🎮 <b>{$level}</b>\n"
            . "🛒 Xaridor: {$buyerName}\n\n"
            . "✉️ Endi <b>shu botga</b> xabar yozing — xaridorga yetkaziladi.\n"
            . "Xaridor to'lovini tasdiqlagan so'ng akkaunt ma'lumotlarini yuboring.\n\n"
            . "⚠️ <b>Akkaunt parolini faqat to'lov kelganidan keyin yuboring!</b>",
            [[['text' => '❌ Bitimdan chiqish', 'callback_data' => "seller_cancel_{$deal->id}"]]]
        );

        // Admin xabarini yangilash — bu sinxron qoladi (admin darhol ko'rishi kerak)
        $this->telegram->editMessageText(
            $adminChatId, $messageId,
            "🤝 <b>Bitim #{$dealId} boshlandi!</b>\n\n"
            . "🎮 {$level}\n"
            . "💰 {$price} so'm\n"
            . "🛒 Xaridor: {$buyerName}\n"
            . "👤 Sotuvchi: {$sellerName}\n\n"
            . "✉️ Xabarlar bot orqali relay qilinmoqda.",
            [[
                ['text' => '✅ Savdo yakunlandi', 'callback_data' => "complete_deal_{$dealId}"],
                ['text' => '❌ Bekor qilish',     'callback_data' => "cancel_deal_{$dealId}"],
            ]]
        );
    }

    private function cancelDeal(int $dealId, int|string $adminChatId, int $messageId): void
    {
        $deal = Deal::with(['account', 'buyer', 'seller'])->find($dealId);

        if (!$deal) {
            $this->telegram->editMessageText($adminChatId, $messageId, "❌ Bitim topilmadi (#$dealId)");
            return;
        }

        if ($deal->status === 'completed') {
            $this->telegram->editMessageText($adminChatId, $messageId, "⚠️ Yakunlangan bitimni bekor qilib bo'lmaydi (#$dealId)");
            return;
        }

        if ($deal->status === 'cancelled') {
            $this->telegram->editMessageText($adminChatId, $messageId, "ℹ️ Bu bitim allaqachon bekor qilingan (#$dealId)");
            return;
        }

        $deal->update(['status' => 'cancelled']);
        $deal->account->update(['status' => 'active']);

        $price = number_format($deal->account->price, 0, '.', ' ');
        $level = $deal->account->collection_level;

        $this->telegram->editMessageText(
            $adminChatId, $messageId,
            "❌ <b>Bitim #{$dealId} bekor qilindi</b>\n\n"
            . "🎮 {$level}\n"
            . "💰 {$price} so'm\n\n"
            . "Akkaunt qayta sotuvga qo'yildi.",
            []
        );

        SendTelegramMessage::dispatch(
            $deal->buyer->telegram_id,
            "❌ <b>Bitim bekor qilindi.</b>\n\n"
            . "🎮 {$level}\n"
            . "💰 {$price} so'm\n\n"
            . "Boshqa akkauntlarni ko'rish uchun marketplace ga kiring."
        );

        SendTelegramMessage::dispatch(
            $deal->seller->telegram_id,
            "ℹ️ <b>Bitim bekor qilindi.</b>\n\n"
            . "🎮 {$level}\n"
            . "💰 {$price} so'm\n\n"
            . "Akkauntingiz yana sotuvga qo'yildi — boshqa xaridorlar ko'ra oladi. ✅"
        );
    }

    // ──────────────────────────────────────────────
    //  DEAL: complete
    // ──────────────────────────────────────────────

    private function completeDeal(int $dealId, int|string $adminChatId, int $messageId): void
    {
        $deal = Deal::with(['account', 'buyer', 'seller'])->find($dealId);

        if (!$deal) {
            $this->telegram->editMessageText($adminChatId, $messageId, "❌ Bitim topilmadi (#$dealId)");
            return;
        }

        if ($deal->status === 'completed') {
            $this->telegram->editMessageText($adminChatId, $messageId, "✅ Bu bitim allaqachon yakunlangan! (#$dealId)");
            return;
        }

        if ($deal->status === 'cancelled') {
            $this->telegram->editMessageText($adminChatId, $messageId, "⚠️ Bekor qilingan bitimni yakunlab bo'lmaydi (#$dealId)");
            return;
        }

        $deal->update(['status' => 'completed']);
        $deal->account->update(['status' => 'sold']);

        $buyerName  = $deal->buyer->username  ? "@{$deal->buyer->username}"  : $deal->buyer->first_name;
        $sellerName = $deal->seller->username ? "@{$deal->seller->username}" : $deal->seller->first_name;
        $price      = number_format($deal->account->price, 0, '.', ' ');
        $level      = $deal->account->collection_level;

        // Kanal xabaridan "Sotib olish" tugmasini olib tashlash
        if ($deal->account->channel_message_id) {
            $this->telegram->editMessageCaption(
                $this->telegram->adminChannelId,
                (int) $deal->account->channel_message_id,
                $this->buildAccountText($deal->account, '✅ SOTILDI'),
                []
            );
        }

        // Admin xabarini yakunlandi deb yangilash
        $this->telegram->editMessageText(
            $adminChatId, $messageId,
            "🏆 <b>Bitim #{$dealId} muvaffaqiyatli yakunlandi!</b>\n\n"
            . "🎮 {$level}\n"
            . "💰 {$price} so'm\n"
            . "🛒 Xaridor: {$buyerName}\n"
            . "👤 Sotuvchi: {$sellerName}",
            []
        );

        SendTelegramMessage::dispatch(
            $deal->buyer->telegram_id,
            "🎉 <b>Tabriklaymiz! Bitim yakunlandi.</b>\n\n"
            . "🎮 {$level}\n"
            . "💰 {$price} so'm\n\n"
            . "Akkaunt muvaffaqiyatli o'tkazildi! Yaxshi o'yinlar! 🚀"
        );

        SendTelegramMessage::dispatch(
            $deal->seller->telegram_id,
            "💰 <b>Akkauntingiz sotildi!</b>\n\n"
            . "🎮 {$level}\n"
            . "💰 {$price} so'm\n"
            . "🛒 Xaridor: {$buyerName}\n\n"
            . "To'lov masalasi bo'yicha admin bilan bog'laning."
        );
    }

    // ──────────────────────────────────────────────
    //  CHECKIN
    // ──────────────────────────────────────────────

    private function checkinConfirm(int $accountId, int $fromTgId, int|string $chatId, int $messageId): void
    {
        $account = Account::with('user')->find($accountId);
        if (!$account) return;

        if ($account->user->telegram_id !== $fromTgId) return;

        $account->update([
            'last_confirmed_at' => now(),
            'checkin_sent_at'   => null,
        ]);

        $this->telegram->editMessageText(
            $chatId, $messageId,
            "✅ <b>Tasdiqlandi!</b>\n\n"
            . "🎮 <b>{$account->collection_level}</b>\n\n"
            . "E'loningiz yana 3 kun davomida faol bo'ladi.",
            []
        );
    }

    private function checkinArchive(int $accountId, int $fromTgId, int|string $chatId, int $messageId): void
    {
        $account = Account::with('user')->find($accountId);
        if (!$account) return;

        if ($account->user->telegram_id !== $fromTgId) return;

        $hasActiveDeal = Deal::where('account_id', $account->id)
            ->whereIn('status', ['pending_admin', 'ongoing'])
            ->exists();

        if ($hasActiveDeal) {
            $this->telegram->editMessageText(
                $chatId, $messageId,
                "⚠️ <b>Arxivlab bo'lmaydi</b>\n\n"
                . "🎮 <b>{$account->collection_level}</b>\n\n"
                . "Bu akkaunt uchun hozir faol bitim bor. Avval bitim yakunlangandan so'ng arxivlang.",
                []
            );
            return;
        }

        $account->update(['status' => 'archived']);

        $this->telegram->editMessageText(
            $chatId, $messageId,
            "📦 <b>E'lon arxivlandi</b>\n\n"
            . "🎮 <b>{$account->collection_level}</b>\n\n"
            . "E'lon bozordan olib tashlandi. Keyinchalik qaytadan joylashtirishingiz mumkin.",
            []
        );
    }

    // ──────────────────────────────────────────────
    //  ACCOUNT REQUEST: approve / reject
    // ──────────────────────────────────────────────

    private function approveRequest(int $reqId, int|string $adminChatId, int $messageId): void
    {
        $db  = \Illuminate\Support\Facades\DB::table('account_requests');
        $req = $db->where('id', $reqId)->first();

        if (!$req) {
            $this->telegram->editMessageText($adminChatId, $messageId, "❌ So'rov topilmadi (#$reqId)");
            return;
        }

        if ($req->status === 'active') {
            $this->telegram->editMessageText($adminChatId, $messageId, "ℹ️ Bu so'rov allaqachon tasdiqlangan.");
            return;
        }

        $db->where('id', $reqId)->update(['status' => 'active', 'updated_at' => now()]);

        $this->telegram->editMessageText(
            $adminChatId, $messageId,
            "✅ <b>So'rov #{$reqId} tasdiqlandi</b> va e'lonlar taxtasiga qo'yildi.",
            []
        );

        $user = User::find($req->user_id);
        if ($user) {
            $token  = $this->makeToken($user);
            $reqUrl = config('app.url') . '/webapp/requests?token=' . $token;
            SendTelegramMessage::dispatch(
                $user->telegram_id,
                "✅ <b>Buyurtmangiz tasdiqlandi!</b>\n\n"
                . "📋 Sizning akkaunt so'rovingiz e'lonlar taxtasiga qo'yildi.\n"
                . "Sotuvchilar javob yoza boshlaydilar. 👇",
                [[['text' => "📋 Buyurtmalar taxtasi", 'url' => $reqUrl]]]
            );
        }
    }

    private function rejectRequest(int $reqId, int|string $adminChatId, int $messageId): void
    {
        $db  = \Illuminate\Support\Facades\DB::table('account_requests');
        $req = $db->where('id', $reqId)->first();

        if (!$req) {
            $this->telegram->editMessageText($adminChatId, $messageId, "❌ So'rov topilmadi (#$reqId)");
            return;
        }

        if ($req->status === 'closed') {
            $this->telegram->editMessageText($adminChatId, $messageId, "ℹ️ Bu so'rov allaqachon rad etilgan (#$reqId)");
            return;
        }

        if ($req->status === 'active') {
            $this->telegram->editMessageText($adminChatId, $messageId, "⚠️ Tasdiqlangan so'rovni rad etib bo'lmaydi (#$reqId)");
            return;
        }

        $db->where('id', $reqId)->update(['status' => 'closed', 'updated_at' => now()]);

        $this->telegram->editMessageText(
            $adminChatId, $messageId,
            "❌ <b>So'rov #{$reqId} rad etildi.</b>",
            []
        );

        $user = \Illuminate\Support\Facades\DB::table('users')->where('id', $req->user_id)->first();
        if ($user) {
            SendTelegramMessage::dispatch(
                $user->telegram_id,
                "❌ <b>Afsuski, buyurtmangiz tasdiqlanmadi.</b>\n\n"
                . "Qayta ko'rib chiqib, yangi buyurtma berishingiz mumkin."
            );
        }
    }

    // ──────────────────────────────────────────────
    //  HELPERS
    // ──────────────────────────────────────────────

    private function makeToken(User $user): string
    {
        // Eski tokenlarni o'chirish (bu user uchun)
        LoginToken::where('user_id', $user->id)->delete();

        // Umuman eskirgan tokenlarni ham tozalash
        LoginToken::where('expires_at', '<', now())->delete();

        $token = Str::random(48);
        LoginToken::create([
            'token'      => $token,
            'user_id'    => $user->id,
            'expires_at' => now()->addHours(24),
        ]);

        return $token;
    }
}
