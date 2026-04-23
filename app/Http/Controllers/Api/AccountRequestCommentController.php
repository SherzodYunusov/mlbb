<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountRequest;
use App\Models\AccountRequestComment;
use App\Models\Comment;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountRequestCommentController extends Controller
{
    public function __construct(private TelegramService $telegram) {}

    // ── Izohlar ro'yxati ──────────────────────────────────
    public function index(Request $request, int $requestId): JsonResponse
    {
        $req = AccountRequest::findOrFail($requestId);

        $viewer = $request->integer('telegram_id')
            ? User::where('telegram_id', $request->integer('telegram_id'))->first()
            : null;

        $comments = AccountRequestComment::with('sender')
            ->where('request_id', $requestId)
            ->latest()
            ->get()
            ->map(fn($c) => $this->format($c, $viewer, $req->user_id));

        return response()->json(['data' => $comments]);
    }

    // ── Izoh qo'shish ─────────────────────────────────────
    public function store(Request $request, int $requestId): JsonResponse
    {
        $request->validate([
            'telegram_id' => ['required', 'integer', 'exists:users,telegram_id'],
            'message'     => ['required', 'string', 'min:1', 'max:500'],
        ]);

        $req    = AccountRequest::with('user')->findOrFail($requestId);
        $sender = User::where('telegram_id', $request->integer('telegram_id'))->firstOrFail();

        // Spam tekshiruvi
        if (Comment::isSpam($request->input('message'))) {
            return response()->json([
                'message' => "Xabar tashqi aloqa ma'lumotlarini o'z ichiga olmasligi kerak.",
            ], 422);
        }

        $comment = AccountRequestComment::create([
            'request_id' => $requestId,
            'sender_id'  => $sender->id,
            'message'    => $request->input('message'),
        ]);

        $comment->load('sender');

        // Buyurtma egasiga bildirishnoma (o'ziga emas)
        if ($req->user_id !== $sender->id && $req->user) {
            $this->notifyPoster($req, $sender, $comment);
        }

        return response()->json([
            'comment' => $this->format($comment, $sender, $req->user_id),
        ], 201);
    }

    // ── Izoh o'chirish ────────────────────────────────────
    public function destroy(Request $request, int $commentId): JsonResponse
    {
        $request->validate([
            'telegram_id' => ['required', 'integer', 'exists:users,telegram_id'],
        ]);

        $sender  = User::where('telegram_id', $request->integer('telegram_id'))->firstOrFail();
        $comment = AccountRequestComment::where('id', $commentId)
                        ->where('sender_id', $sender->id)
                        ->firstOrFail();

        if (!$comment->canEdit()) {
            return response()->json(['message' => "O'chirish muddati o'tdi (15 daqiqa)"], 422);
        }

        $comment->delete();

        return response()->json(['success' => true]);
    }

    // ── Helpers ───────────────────────────────────────────

    private function format(AccountRequestComment $c, ?User $viewer, int $posterId): array
    {
        $isMine   = $viewer && $c->sender_id === $viewer->id;
        $isPoster = $c->sender_id === $posterId;
        $name     = $c->sender->username
                    ? '@' . $c->sender->username
                    : $c->sender->first_name;

        return [
            'id'         => $c->id,
            'message'    => $c->message,
            'created_at' => $c->created_at->toISOString(),
            'is_mine'    => $isMine,
            'is_poster'  => $isPoster,
            'can_delete' => $isMine && $c->canEdit(),
            'sender'     => [
                'name'      => $name,
                'initials'  => mb_strtoupper(mb_substr($c->sender->first_name ?? 'U', 0, 1)),
                'tg_id'     => $c->sender->telegram_id,
            ],
        ];
    }

    private function notifyPoster(AccountRequest $req, User $sender, AccountRequestComment $comment): void
    {
        $senderName = $sender->username ? "@{$sender->username}" : $sender->first_name;
        $preview    = mb_strlen($comment->message) > 80
                      ? mb_substr($comment->message, 0, 80) . '...'
                      : $comment->message;

        try {
            $this->telegram->sendMessage(
                $req->user->telegram_id,
                "💬 <b>Buyurtmangizga javob keldi!</b>\n\n"
                . "👤 <b>{$senderName}</b> yozdi:\n"
                . "<i>{$preview}</i>"
            );
        } catch (\Throwable) {}
    }
}
