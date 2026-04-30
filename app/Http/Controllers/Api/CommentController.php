<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Comment;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(private TelegramService $telegram) {}

    // ── Izohlarni olish ──────────────────────────────
    public function index(Request $request, int $accountId): JsonResponse
    {
        $account = Account::with('user')->findOrFail($accountId);

        $viewer = $request->integer('telegram_id')
            ? User::where('telegram_id', $request->integer('telegram_id'))->first()
            : null;

        $comments = Comment::with(['sender', 'replies.sender'])
            ->where('account_id', $accountId)
            ->whereNull('parent_id')
            ->where('is_hidden', false)
            ->latest()
            ->get()
            ->map(fn($c) => $this->format($c, $viewer, $account->user_id));

        return response()->json(['data' => $comments]);
    }

    // ── Izoh qoldirish ───────────────────────────────
    public function store(Request $request, int $accountId): JsonResponse
    {
        $request->validate([
            'telegram_id' => ['required', 'integer', 'exists:users,telegram_id'],
            'message'     => ['required', 'string', 'min:1', 'max:500'],
            'parent_id'   => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        $account = Account::with('user')->findOrFail($accountId);
        $sender  = User::where('telegram_id', $request->integer('telegram_id'))->firstOrFail();

        // Spam tekshiruvi
        if (Comment::isSpam($request->input('message'))) {
            return response()->json([
                'message' => "Xabar tashqi aloqa ma'lumotlarini o'z ichiga olmasligi kerak.",
            ], 422);
        }

        // parent_id shu account ga tegishli bo'lishi kerak
        if ($request->filled('parent_id')) {
            $parent = Comment::where('id', $request->integer('parent_id'))
                             ->where('account_id', $accountId)
                             ->whereNull('parent_id') // faqat birinchi darajaga javob
                             ->first();
            if (!$parent) {
                return response()->json(['message' => 'Noto\'g\'ri javob'], 422);
            }
        }

        $comment = Comment::create([
            'account_id' => $accountId,
            'sender_id'  => $sender->id,
            'parent_id'  => $request->integer('parent_id') ?: null,
            'message'    => $request->input('message'),
        ]);

        $comment->load(['sender', 'parent.sender']);

        // Sotuvchiga bildirishnoma (o'z izohiga emas)
        if ($account->user_id !== $sender->id) {
            $this->notifySeller($account, $sender, $comment);
        }

        // Javob bo'lsa — asl izoh egasiga bildirishnoma
        // (o'ziga emas, sotuvchiga allaqachon yuborilgan bo'lsa ham emas)
        if ($comment->parent && $comment->parent->sender) {
            $parentAuthor = $comment->parent->sender;
            $notAlreadyNotified = $parentAuthor->id !== $account->user_id
                               && $parentAuthor->id !== $sender->id;
            if ($notAlreadyNotified) {
                $this->notifyReply($account, $sender, $comment);
            }
        }

        // Adminga bildirishnoma (moderatsiya uchun)
        $this->notifyAdmin($account, $sender, $comment);

        return response()->json([
            'comment' => $this->format($comment, $sender, $account->user_id),
        ], 201);
    }

    // ── Izohni tahrirlash (15 daqiqa ichida) ─────────
    public function update(Request $request, int $commentId): JsonResponse
    {
        $request->validate([
            'telegram_id' => ['required', 'integer', 'exists:users,telegram_id'],
            'message'     => ['required', 'string', 'min:1', 'max:500'],
        ]);

        $sender  = User::where('telegram_id', $request->integer('telegram_id'))->firstOrFail();
        $comment = Comment::where('id', $commentId)
                          ->where('sender_id', $sender->id)
                          ->firstOrFail();

        if (!$comment->canEdit()) {
            return response()->json(['message' => 'Tahrirlash muddati o\'tdi (15 daqiqa)'], 422);
        }

        if (Comment::isSpam($request->input('message'))) {
            return response()->json([
                'message' => "Xabar tashqi aloqa ma'lumotlarini o'z ichiga olmasligi kerak.",
            ], 422);
        }

        $comment->update([
            'message'   => $request->input('message'),
            'edited_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // ── Izohni o'chirish (15 daqiqa ichida) ─────────
    public function destroy(Request $request, int $commentId): JsonResponse
    {
        $request->validate([
            'telegram_id' => ['required', 'integer', 'exists:users,telegram_id'],
        ]);

        $sender  = User::where('telegram_id', $request->integer('telegram_id'))->firstOrFail();
        $comment = Comment::where('id', $commentId)
                          ->where('sender_id', $sender->id)
                          ->firstOrFail();

        if (!$comment->canEdit()) {
            return response()->json(['message' => 'O\'chirish muddati o\'tdi (15 daqiqa)'], 422);
        }

        $comment->delete();

        return response()->json(['success' => true]);
    }

    // ── Helpers ─────────────────────────────────────

    private function format(Comment $c, User|null $viewer, int $sellerId): array
    {
        $isMine   = $viewer && $c->sender_id === $viewer->id;
        $isSeller = $c->sender_id === $sellerId;
        $name     = $c->sender->username
                    ? '@' . $c->sender->username
                    : $c->sender->first_name;

        return [
            'id'         => $c->id,
            'message'    => $c->message,
            'created_at' => $c->created_at->toISOString(),
            'edited_at'  => $c->edited_at?->toISOString(),
            'is_mine'    => $isMine,
            'is_seller'  => $isSeller,
            'can_edit'   => $isMine && $c->canEdit(),
            'sender'     => [
                'name'     => $name,
                'initials' => mb_strtoupper(mb_substr($c->sender->first_name ?? 'U', 0, 1)),
                'tg_id'    => $c->sender->telegram_id,
            ],
            'replies'    => $c->relationLoaded('replies')
                ? $c->replies->map(fn($r) => $this->format($r, $viewer, $sellerId))->values()
                : [],
        ];
    }

    private function notifyReply(Account $account, User $sender, Comment $comment): void
    {
        $parentAuthor = $comment->parent->sender;
        $senderName   = $sender->username ? "@{$sender->username}" : $sender->first_name;
        $preview      = mb_strlen($comment->message) > 80
                        ? mb_substr($comment->message, 0, 80) . '...'
                        : $comment->message;

        try {
            $this->telegram->sendMessage(
                $parentAuthor->telegram_id,
                "↩️ <b>Izohingizga javob keldi!</b>\n\n"
                . "🎮 Akkaunt: <b>{$account->collection_level}</b>\n"
                . "👤 <b>{$senderName}</b> yozdi:\n"
                . "<i>{$preview}</i>"
            );
        } catch (\Throwable) {}
    }

    private function notifyAdmin(Account $account, User $sender, Comment $comment): void
    {
        $adminId = config('services.telegram.admin_id');
        if (!$adminId) return;

        $senderName = $sender->username ? "@{$sender->username}" : $sender->first_name;
        $preview    = mb_strlen($comment->message) > 100
                      ? mb_substr($comment->message, 0, 100) . '...'
                      : $comment->message;

        try {
            $this->telegram->sendMessage(
                $adminId,
                "💬 <b>Yangi izoh (#{$comment->id})</b>\n\n"
                . "🎮 Akkaunt: <b>{$account->collection_level}</b>\n"
                . "👤 {$senderName} yozdi:\n"
                . "<i>{$preview}</i>",
                [[['text' => '🚫 Yashirish', 'callback_data' => "hide_comment_{$comment->id}"]]]
            );
        } catch (\Throwable) {}
    }

    private function notifySeller(Account $account, User $sender, Comment $comment): void
    {
        $senderName = $sender->username ? "@{$sender->username}" : $sender->first_name;
        $preview    = mb_strlen($comment->message) > 80
                      ? mb_substr($comment->message, 0, 80) . '...'
                      : $comment->message;

        try {
            $this->telegram->sendMessage(
                $account->user->telegram_id,
                "💬 <b>Yangi izoh!</b>\n\n"
                . "🎮 Akkaunt: <b>{$account->collection_level}</b>\n"
                . "👤 <b>{$senderName}</b> yozdi:\n"
                . "<i>{$preview}</i>"
            );
        } catch (\Throwable) {
            // Telegram xato bersa ham davom etamiz
        }
    }
}
