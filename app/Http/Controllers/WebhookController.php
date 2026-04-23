<?php

namespace App\Http\Controllers;

use App\Models\Account;
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

        $keyboard = [
            [['text' => '🛒 MLBB Marketplace',     'url' => $webUrl]],
            [['text' => '👤 Mening profilim',       'url' => $profileUrl]],
            [['text' => '📋 Mening e\'lonlarim',    'callback_data' => 'my_accounts']],
            [['text' => '🔍 Akkaunt qidiruv',       'url' => config('app.url') . '/webapp/requests']],
        ];

        $this->telegram->sendMessage($chatId, $text, $keyboard);
    }

    // ──────────────────────────────────────────────
    //  CALLBACK
    // ──────────────────────────────────────────────

    private function handleCallback(array $callback): void
    {
        $chatId      = $callback['message']['chat']['id'];
        $messageId   = $callback['message']['message_id'];
        $from        = $callback['from'];
        $data        = $callback['data'] ?? '';
        $callbackId  = $callback['id'];
        $isAdmin     = $from['id'] === $this->telegram->adminId;

        $this->telegram->answerCallbackQuery($callbackId);

        \Illuminate\Support\Facades\Log::info('Callback', [
            'data'        => $data,
            'from'        => $from['id'],
            'configAdmin' => $this->telegram->adminId,
            'isAdmin'     => $isAdmin,
            'chatId'      => $chatId,
        ]);

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
            $this->checkinConfirm($accountId, $chatId, $messageId);
            return;
        }

        // ── Checkin: sotib yubordim → arxivlash ──
        if (str_starts_with($data, 'checkin_sold_')) {
            $accountId = (int) substr($data, 13);
            $this->checkinArchive($accountId, $chatId, $messageId);
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

        // Admin xabarini yangilash
        $this->telegram->editMessageText(
            $adminChatId, $messageId,
            "✅ <b>Tasdiqlandi!</b> Akkaunt #{$accountId} sotuvga qo'yildi.",
            []
        );

        // Sotuvchiga xabar (foydalanuvchi o'chirilgan bo'lishi mumkin)
        $seller = $account->user;
        if (!$seller) return;

        $token = $this->makeToken($seller);
        $url   = config('app.url') . '/webapp?token=' . $token . '&tab=profile';

        $this->telegram->sendMessage(
            $seller->telegram_id,
            "✅ <b>Akkauntingiz tasdiqlandi!</b>\n\n"
            . "🏆 {$account->collection_level}\n"
            . "💰 " . number_format($account->price, 0, '.', ' ') . " so'm\n\n"
            . "Akkauntingiz endi marketplace da ko'rinmoqda.",
            [[['text' => "🛒 Marketplace'ga o'tish", 'url' => $url]]]
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

        // Sotuvchiga xabar (foydalanuvchi o'chirilgan bo'lishi mumkin)
        if (!$account->user) return;

        $this->telegram->sendMessage(
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

        $deal->update(['status' => 'ongoing']);

        $buyerName  = $deal->buyer->username  ? "@{$deal->buyer->username}"  : $deal->buyer->first_name;
        $sellerName = $deal->seller->username ? "@{$deal->seller->username}" : $deal->seller->first_name;
        $price      = number_format($deal->account->price, 0, '.', ' ');
        $dealsGroupId = config('services.telegram.deals_group_id');

        // ── Forum topic yaratish (deals guruhida) ──
        $topicName = "Deal #{$dealId} — {$deal->account->collection_level}";
        $topicId   = $dealsGroupId
            ? $this->telegram->createForumTopic($dealsGroupId, $topicName)
            : null;

        if ($topicId) {
            $deal->update(['group_chat_id' => $dealsGroupId, 'topic_id' => $topicId]);

            // Guruhga xabar yuborish
            $this->telegram->sendMessage(
                $dealsGroupId,
                "🤝 <b>Yangi bitim #{$dealId}</b>\n\n"
                . "💰 Narx: <b>{$price} so'm</b>\n"
                . "🏆 {$deal->account->collection_level}\n\n"
                . "🛒 Xaridor: {$buyerName}\n"
                . "👤 Sotuvchi: {$sellerName}",
                [],
                $topicId
            );

            // Invite linklar yaratish
            $buyerLink  = $this->telegram->createInviteLink($dealsGroupId, "Buyer #{$dealId}");
            $sellerLink = $this->telegram->createInviteLink($dealsGroupId, "Seller #{$dealId}");

            // Xaridorga guruh linki
            $this->telegram->sendMessage(
                $deal->buyer->telegram_id,
                "🤝 <b>Sotib olish tasdiqlandi!</b>\n\n"
                . "💰 Narx: <b>{$price} so'm</b>\n"
                . "🏆 {$deal->account->collection_level}\n\n"
                . "Quyidagi tugma orqali maxsus guruhga kiring:",
                $buyerLink ? [[['text' => '👥 Guruhga kirish', 'url' => $buyerLink]]] : []
            );

            // Sotuvchiga guruh linki
            $this->telegram->sendMessage(
                $deal->seller->telegram_id,
                "🔔 <b>Akkauntingizni sotib olmoqchi!</b>\n\n"
                . "💰 Narx: <b>{$price} so'm</b>\n"
                . "🛒 Xaridor: {$buyerName}\n\n"
                . "Quyidagi tugma orqali maxsus guruhga kiring:",
                $sellerLink ? [[['text' => '👥 Guruhga kirish', 'url' => $sellerLink]]] : []
            );

        } else {
            // Guruh sozlanmagan — admin username yuboriladi
            $adminUsername = config('services.telegram.admin_username', 'admin');

            $this->telegram->sendMessage(
                $deal->buyer->telegram_id,
                "🤝 <b>Sotib olish tasdiqlandi!</b>\n\n"
                . "💰 Narx: <b>{$price} so'm</b>\n"
                . "Admin tez orada bog'lanadi: @{$adminUsername}"
            );

            $this->telegram->sendMessage(
                $deal->seller->telegram_id,
                "🔔 <b>Akkauntingizni sotib olishmoqchi!</b>\n\n"
                . "🛒 Xaridor: {$buyerName}\n"
                . "Admin tez orada bog'lanadi: @{$adminUsername}"
            );
        }

        // Admin xabarini yangilash + "Yakunlash" tugmasi
        $this->telegram->editMessageText(
            $adminChatId, $messageId,
            "🤝 <b>Bitim #{$dealId} boshlandi!</b>\n\n"
            . "🏆 {$deal->account->collection_level}\n"
            . "💰 {$price} so'm\n"
            . "🛒 Xaridor: {$buyerName}\n"
            . "👤 Sotuvchi: {$sellerName}\n\n"
            . ($topicId ? "✅ Guruh yaratildi, ikkalasiga link yuborildi." : "📩 Admin orqali bog'lanish."),
            [[
                ['text' => '✅ Yakunlash',     'callback_data' => "complete_deal_{$dealId}"],
                ['text' => '❌ Bekor qilish',  'callback_data' => "cancel_deal_{$dealId}"],
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

        $deal->update(['status' => 'cancelled']);

        // Admin xabarini yangilash
        $this->telegram->editMessageText(
            $adminChatId, $messageId,
            "❌ <b>Bitim rad etildi.</b> Deal #{$dealId}",
            []
        );

        // Xaridorga xabar
        $this->telegram->sendMessage(
            $deal->buyer->telegram_id,
            "❌ <b>Sotib olish so'rovingiz bekor qilindi.</b>\n\n"
            . "Boshqa akkauntlarni ko'rish uchun marketplace ga kiring."
        );

        // Sotuvchiga xabar
        $this->telegram->sendMessage(
            $deal->seller->telegram_id,
            "ℹ️ <b>Bitim bekor qilindi.</b>\n\n"
            . "🏆 {$deal->account->collection_level}\n\n"
            . "Akkauntingiz yana sotuvda davom etadi."
        );
    }

    // ──────────────────────────────────────────────
    //  DEAL: complete
    // ──────────────────────────────────────────────

    private function completeDeal(int $dealId, int|string $adminChatId, int $messageId): void
    {
        $deal = Deal::with(['account', 'buyer', 'seller'])->find($dealId);

        if (!$deal) return;

        if ($deal->status === 'completed') {
            $this->telegram->editMessageText($adminChatId, $messageId, "✅ Bu bitim allaqachon yakunlangan! (#$dealId)");
            return;
        }

        $deal->update(['status' => 'completed']);
        $deal->account->update(['status' => 'sold']);

        $buyerName  = $deal->buyer->username  ? "@{$deal->buyer->username}"  : $deal->buyer->first_name;
        $sellerName = $deal->seller->username ? "@{$deal->seller->username}" : $deal->seller->first_name;
        $price      = number_format($deal->account->price, 0, '.', ' ');
        $level      = $deal->account->collection_level;

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

        // Xaridorga tabrik
        $this->telegram->sendMessage(
            $deal->buyer->telegram_id,
            "🎉 <b>Tabriklaymiz! Bitim yakunlandi.</b>\n\n"
            . "🎮 {$level}\n"
            . "💰 {$price} so'm\n\n"
            . "Akkaunt muvaffaqiyatli o'tkazildi! Yaxshi o'yinlar! 🚀"
        );

        // Sotuvchiga xabar
        $this->telegram->sendMessage(
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

    private function checkinConfirm(int $accountId, int|string $chatId, int $messageId): void
    {
        $account = Account::find($accountId);
        if (!$account) return;

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

    private function checkinArchive(int $accountId, int|string $chatId, int $messageId): void
    {
        $account = Account::find($accountId);
        if (!$account) return;

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
        \Illuminate\Support\Facades\Log::info('approveRequest', [
            'reqId'       => $reqId,
            'adminChatId' => $adminChatId,
            'messageId'   => $messageId,
        ]);

        $req = AccountRequest::with('user')->find($reqId)
            ?? AccountRequest::with('user')->where('id', $reqId)->first();

        // Diagnostics: agar hali ham topilmasa
        if (!$req) {
            $raw = \Illuminate\Support\Facades\DB::table('account_requests')->where('id', $reqId)->first();
            \Illuminate\Support\Facades\Log::warning('approveRequest: not found', [
                'reqId'    => $reqId,
                'dbRaw'    => $raw ? ['status' => $raw->status] : null,
                'dbExists' => $raw ? true : false,
            ]);
        }

        \Illuminate\Support\Facades\Log::info('approveRequest find result', [
            'found'  => $req ? true : false,
            'status' => $req?->status,
        ]);

        if (!$req) {
            $this->telegram->editMessageText($adminChatId, $messageId, "❌ So'rov topilmadi (#$reqId)");
            return;
        }

        if ($req->status === 'active') {
            $this->telegram->editMessageText($adminChatId, $messageId, "ℹ️ Bu so'rov allaqachon tasdiqlangan.");
            return;
        }

        $req->update(['status' => 'active']);

        $this->telegram->editMessageText(
            $adminChatId, $messageId,
            "✅ <b>So'rov #{$reqId} tasdiqlandi</b> va e'lonlar taxtasiga qo'yildi.",
            []
        );

        if ($req->user) {
            $reqUrl = config('app.url') . '/webapp/requests';
            $this->telegram->sendMessage(
                $req->user->telegram_id,
                "✅ <b>Buyurtmangiz tasdiqlandi!</b>\n\n"
                . "📋 Sizning akkaunt so'rovingiz e'lonlar taxtasiga qo'yildi.\n"
                . "Sotuvchilar javob yoza boshlaydilar. 👇",
                [[['text' => "📋 Buyurtmalar taxtasi", 'url' => $reqUrl]]]
            );
        }
    }

    private function rejectRequest(int $reqId, int|string $adminChatId, int $messageId): void
    {
        \Illuminate\Support\Facades\Log::info('rejectRequest', [
            'reqId'       => $reqId,
            'adminChatId' => $adminChatId,
            'messageId'   => $messageId,
        ]);

        $req = AccountRequest::with('user')->find($reqId)
            ?? AccountRequest::with('user')->where('id', $reqId)->first();

        // Diagnostics: agar hali ham topilmasa
        if (!$req) {
            $raw = \Illuminate\Support\Facades\DB::table('account_requests')->where('id', $reqId)->first();
            \Illuminate\Support\Facades\Log::warning('rejectRequest: not found', [
                'reqId'    => $reqId,
                'dbRaw'    => $raw ? ['status' => $raw->status] : null,
                'dbExists' => $raw ? true : false,
            ]);
        }

        \Illuminate\Support\Facades\Log::info('rejectRequest find result', [
            'found'  => $req ? true : false,
            'status' => $req?->status,
        ]);

        if (!$req) {
            $this->telegram->editMessageText($adminChatId, $messageId, "❌ So'rov topilmadi (#$reqId)");
            return;
        }

        $req->update(['status' => 'closed']);

        $this->telegram->editMessageText(
            $adminChatId, $messageId,
            "❌ <b>So'rov #{$reqId} rad etildi.</b>",
            []
        );

        if ($req->user) {
            $this->telegram->sendMessage(
                $req->user->telegram_id,
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
