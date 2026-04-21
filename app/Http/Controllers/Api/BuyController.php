<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Deal;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BuyController extends Controller
{
    public function __construct(private TelegramService $telegram) {}

    public function buy(Request $request, int $accountId): JsonResponse
    {
        $request->validate([
            'telegram_id' => ['required', 'integer', 'exists:users,telegram_id'],
        ]);

        $account = Account::with('user')->find($accountId);

        if (!$account || $account->status !== 'active') {
            return response()->json(['error' => 'Akkaunt sotuvda emas'], 404);
        }

        $buyer = User::where('telegram_id', $request->integer('telegram_id'))->firstOrFail();

        if ($account->user->telegram_id === $buyer->telegram_id) {
            return response()->json(['error' => 'O\'z akkauntingizni sotib ololmaysiz'], 422);
        }

        $exists = Deal::where('account_id', $accountId)
            ->whereIn('status', ['pending_admin', 'ongoing'])
            ->exists();

        if ($exists) {
            return response()->json(['error' => 'Bu akkaunt uchun bitim allaqachon jarayonda'], 422);
        }

        $deal = Deal::create([
            'account_id' => $account->id,
            'buyer_id'   => $buyer->id,
            'seller_id'  => $account->user->id,
            'status'     => 'pending_admin',
        ]);

        $price      = number_format($account->price, 0, '.', ' ');
        $buyerName  = $buyer->username  ? "@{$buyer->username}"         : $buyer->first_name;
        $sellerName = $account->user->username ? "@{$account->user->username}" : $account->user->first_name;

        $adminText = "🔔 <b>Yangi sotib olish so'rovi!</b>\n\n"
                   . "💰 <b>Akkaunt #{$account->id}</b>\n"
                   . "📦 Narx: <b>{$price} so'm</b>\n"
                   . "🏆 {$account->collection_level}\n\n"
                   . "🛒 <b>Xaridor:</b> {$buyerName}\n"
                   . "👤 <b>Sotuvchi:</b> {$sellerName}\n\n"
                   . "Bitim guruhini ochasizmi?";

        $keyboard = [[
            ['text' => '🤝 Guruh yaratish', 'callback_data' => "open_deal_{$deal->id}"],
            ['text' => '❌ Rad etish',       'callback_data' => "cancel_deal_{$deal->id}"],
        ]];

        $adminMsgId = $this->telegram->sendMessage($this->telegram->adminId, $adminText, $keyboard);
        $deal->update(['admin_message_id' => $adminMsgId]);

        return response()->json([
            'success' => true,
            'message' => 'So\'rovingiz adminga yuborildi! Tez orada bog\'lanishadi.',
        ]);
    }
}
