<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccountRequest;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountRequestController extends Controller
{
    private const PER_PAGE = 15;

    public function __construct(private TelegramService $telegram) {}

    // ── Tasdiqlangan buyurtmalar ro'yxati ──────────────────
    public function index(Request $request): JsonResponse
    {
        $page  = max(1, $request->integer('page', 1));
        $query = AccountRequest::with(['user', 'comments'])
            ->active()
            ->latest();

        $paginator = $query->paginate(self::PER_PAGE, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(fn(AccountRequest $r) => $this->format($r));

        return response()->json([
            'data'      => $data,
            'has_more'  => $paginator->hasMorePages(),
            'next_page' => $page + 1,
            'total'     => $paginator->total(),
        ]);
    }

    // ── Yangi buyurtma yaratish ────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'telegram_id' => ['required', 'integer', 'exists:users,telegram_id'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'budget_min'  => ['nullable', 'numeric', 'min:0'],
            'budget_max'  => ['nullable', 'numeric', 'min:0'],
            'contact'     => ['nullable', 'string', 'max:64'],
        ]);

        $user = User::where('telegram_id', $request->integer('telegram_id'))->firstOrFail();

        // Foydalanuvchining "pending" buyurtmasi bormi? (spam old.)
        $pending = AccountRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'active'])
            ->count();

        if ($pending >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Siz allaqachon 3 ta faol buyurtmaga egasiz.',
            ], 422);
        }

        $req = AccountRequest::create([
            'user_id'     => $user->id,
            'description' => $request->input('description'),
            'budget_min'  => $request->filled('budget_min') ? $request->input('budget_min') : null,
            'budget_max'  => $request->filled('budget_max') ? $request->input('budget_max') : null,
            'contact'     => $request->filled('contact')
                             ? ltrim($request->input('contact'), '@')
                             : null,
            'status'      => 'pending',
        ]);

        // Admin ga bildirishnoma
        $this->notifyAdmin($user, $req);

        return response()->json([
            'success' => true,
            'message' => 'Buyurtma yuborildi. Admin tasdiqlashini kuting.',
        ], 201);
    }

    // ── O'z buyurtmasini yopish ────────────────────────────
    public function close(Request $request, int $id): JsonResponse
    {
        $request->validate(['telegram_id' => ['required', 'integer']]);

        $user = User::where('telegram_id', $request->integer('telegram_id'))->firstOrFail();
        $req  = AccountRequest::where('id', $id)->where('user_id', $user->id)->firstOrFail();

        $req->update(['status' => 'closed']);

        return response()->json(['success' => true]);
    }

    // ── Helpers ───────────────────────────────────────────

    public function format(AccountRequest $r): array
    {
        $name = $r->user->username
            ? '@' . $r->user->username
            : $r->user->first_name;

        return [
            'id'             => $r->id,
            'description'    => $r->description,
            'budget_min'     => $r->budget_min ? (float) $r->budget_min : null,
            'budget_max'     => $r->budget_max ? (float) $r->budget_max : null,
            'contact'        => $r->contact,
            'status'         => $r->status,
            'poster_name'    => $name,
            'poster_tg_id'   => $r->user->telegram_id,
            'comments_count' => $r->comments->count(),
            'created_at'     => $r->created_at->toISOString(),
        ];
    }

    private function notifyAdmin(User $user, AccountRequest $req): void
    {
        $adminId = $this->telegram->adminId;
        if (!$adminId) return;

        $name    = $user->username ? "@{$user->username}" : $user->first_name;
        $budget  = match(true) {
            $req->budget_min && $req->budget_max =>
                number_format($req->budget_min, 0, '.', ' ') . ' – ' .
                number_format($req->budget_max, 0, '.', ' ') . ' so\'m',
            $req->budget_max =>
                'max ' . number_format($req->budget_max, 0, '.', ' ') . ' so\'m',
            $req->budget_min =>
                'min ' . number_format($req->budget_min, 0, '.', ' ') . ' so\'m',
            default => 'ko\'rsatilmagan',
        };

        $text = "🔍 <b>Yangi akkaunt so'rovi #{$req->id}</b>\n\n"
              . "👤 Foydalanuvchi: {$name}\n"
              . "💰 Byudjet: {$budget}\n"
              . ($req->contact ? "📱 Aloqa: @{$req->contact}\n" : '')
              . "\n📝 <b>Tavsif:</b>\n<i>{$req->description}</i>";

        $keyboard = [[
            ['text' => '✅ Tasdiqlash',  'callback_data' => "approve_req_{$req->id}"],
            ['text' => '❌ Rad etish',   'callback_data' => "reject_req_{$req->id}"],
        ]];

        $msgId = $this->telegram->sendMessage($adminId, $text, $keyboard);

        if ($msgId) {
            $req->update(['admin_message_id' => $msgId]);
        }
    }
}
