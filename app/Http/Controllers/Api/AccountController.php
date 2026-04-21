<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyAdminJob;
use App\Models\Account;
use App\Models\AccountMedia;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image as Img;

class AccountController extends Controller
{
    private const PER_PAGE = 12;

    public function index(Request $request): JsonResponse
    {
        $page  = max(1, $request->integer('page', 1));
        $query = Account::with(['user', 'media'])->active();

        // ── Backend qidiruv ──
        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('collection_level', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u
                      ->where('first_name', 'like', "%{$s}%")
                      ->orWhere('username',   'like', "%{$s}%"));
            });
        }

        // ── Rank filtri ──
        if ($request->filled('rank') && $request->input('rank') !== 'all') {
            $query->where('collection_level', 'like', '%' . $request->input('rank') . '%');
        }

        // ── Narx filtri ──
        if ($request->filled('price_min')) {
            $query->where('price', '>=', (float) $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', (float) $request->input('price_max'));
        }

        // ── Saralash ──
        match ($request->input('sort', 'newest')) {
            'price_asc'   => $query->orderBy('price', 'asc'),
            'price_desc'  => $query->orderBy('price', 'desc'),
            'views_desc'  => $query->orderBy('views', 'desc'),
            'oldest'      => $query->orderBy('created_at', 'asc'),
            default       => $query->orderBy('created_at', 'desc'),
        };

        $paginator = $query->paginate(self::PER_PAGE, ['*'], 'page', $page);

        $data = $paginator->getCollection()->map(fn(Account $a) => [
            'id'                  => $a->id,
            'price'               => (float) $a->price,
            'collection_level'    => $a->collection_level,
            'heroes_count'        => $a->heroes_count,
            'skins_count'         => $a->skins_count,
            'description'         => $a->description,
            'ready_for_transfer'  => $a->ready_for_transfer,
            'seller_name'         => $a->user->username
                                     ? '@' . $a->user->username
                                     : $a->user->first_name,
            'seller_telegram_id'  => $a->user->telegram_id,
            // Katalog uchun: kichik thumbnail (400px)
            'thumbnail'           => ($thumb = $a->media->where('type', 'thumbnail')->first())
                                     ? Storage::url($thumb->file_id)
                                     : (($img = $a->media->where('type', 'image')->first())
                                        ? Storage::url($img->file_id) : null),
            // Detail modal uchun: to'liq rasmlar (800px)
            'images'              => $a->media->where('type', 'image')
                                     ->map(fn($m) => Storage::url($m->file_id))
                                     ->values()->toArray(),
            'video'               => ($vid = $a->media->where('type', 'video')->first())
                                     ? Storage::url($vid->file_id) : null,
            'video_size'          => $a->video_size ? (float) $a->video_size : null,
            'views'               => (int) $a->views,
            'created_at'          => $a->created_at->toISOString(),
        ]);

        return response()->json([
            'data'      => $data,
            'has_more'  => $paginator->hasMorePages(),
            'next_page' => $page + 1,
            'total'     => $paginator->total(),
        ]);
    }

    public function mine(Request $request): JsonResponse
    {
        $request->validate([
            'telegram_id' => ['required', 'integer'],
        ]);

        $user = User::where('telegram_id', $request->integer('telegram_id'))->first();

        if (!$user) {
            return response()->json(['data' => []]);
        }

        $accounts = Account::with(['media'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn(Account $a) => [
                'id'               => $a->id,
                'price'            => (float) $a->price,
                'collection_level' => $a->collection_level,
                'heroes_count'     => $a->heroes_count,
                'skins_count'      => $a->skins_count,
                'status'           => $a->status,
                'views'            => (int) $a->views,
                'created_at'       => $a->created_at->diffForHumans(),
                'thumbnail'        => ($thumb = $a->media->where('type', 'thumbnail')->first())
                                      ? Storage::url($thumb->file_id)
                                      : (($img = $a->media->where('type', 'image')->first())
                                         ? Storage::url($img->file_id) : null),
            ]);

        return response()->json(['data' => $accounts]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'telegram_id'        => ['required', 'integer', 'exists:users,telegram_id'],
            'price'              => ['required', 'numeric', 'min:1000'],
            'heroes_count'       => ['required', 'integer', 'min:0', 'max:131'],
            'skins_count'        => ['required', 'integer', 'min:0', 'max:1070'],
            'collection_level'   => ['required', 'string', 'max:100'],
            'description'        => ['nullable', 'string', 'max:2000'],
            'ready_for_transfer' => ['sometimes', 'boolean'],
            'images'             => ['nullable', 'array', 'max:5'],
            'images.*'           => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'video'              => [
                'nullable', 'file', 'max:51200',
                function ($attribute, $value, $fail) {
                    if ($value === null) return;
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['mp4', 'mov', 'avi', 'webm', 'qt'])) {
                        $fail('Video formati noto\'g\'ri. Qo\'llab-quvvatlanadigan: MP4, MOV, AVI, WEBM.');
                    }
                },
            ],
        ]);

        $user = User::where('telegram_id', $request->integer('telegram_id'))->firstOrFail();

        try {
            $result = DB::transaction(function () use ($request, $user): array {
                $account = Account::create([
                    'user_id'            => $user->id,
                    'price'              => $request->input('price'),
                    'heroes_count'       => $request->integer('heroes_count'),
                    'skins_count'        => $request->integer('skins_count'),
                    'collection_level'   => $request->input('collection_level'),
                    'description'        => $request->input('description'),
                    'ready_for_transfer' => $request->boolean('ready_for_transfer'),
                    'status'             => 'pending',
                ]);

                $paths = [];

                /** @var UploadedFile $image */
                foreach ($request->file('images', []) as $image) {
                    $uid      = uniqid();
                    $mainPath = "accounts/{$account->id}/img_{$uid}.webp";
                    $thumbPath= "accounts/{$account->id}/thumb_{$uid}.webp";

                    try {
                        // Asosiy rasm: 800px, 70% WebP
                        Storage::disk('public')->put(
                            $mainPath,
                            Img::read($image->getPathname())->scaleDown(800, 800)->toWebp(quality: 70)
                        );
                        // Thumbnail: 400px, 65% WebP (katalog uchun)
                        Storage::disk('public')->put(
                            $thumbPath,
                            Img::read($image->getPathname())->scaleDown(400, 400)->toWebp(quality: 65)
                        );
                    } catch (\Throwable) {
                        // Intervention Image xato bersa — asl faylni saqla
                        $mainPath  = $image->store("accounts/{$account->id}", 'public');
                        $thumbPath = null;
                    }

                    AccountMedia::create(['account_id' => $account->id, 'file_id' => $mainPath,  'type' => 'image']);
                    if ($thumbPath) {
                        AccountMedia::create(['account_id' => $account->id, 'file_id' => $thumbPath, 'type' => 'thumbnail']);
                    }
                    $paths[] = $mainPath;
                }

                if ($request->hasFile('video')) {
                    $video     = $request->file('video');
                    $videoSize = round($video->getSize() / (1024 * 1024), 1); // MB
                    $ext       = strtolower($video->getClientOriginalExtension()) ?: 'mp4';
                    $path      = $video->storeAs(
                        "accounts/{$account->id}",
                        'video_' . time() . '.' . $ext,
                        'public'
                    );
                    AccountMedia::create(['account_id' => $account->id, 'file_id' => $path, 'type' => 'video']);
                    $account->update(['video_size' => $videoSize]);
                    $paths[] = $path;
                }

                return ['account' => $account, 'paths' => $paths];
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Account store error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Saqlashda xato: ' . $e->getMessage(),
            ], 500);
        }

        NotifyAdminJob::dispatch($result['account'], $result['paths']);

        return response()->json([
            'success'    => true,
            'message'    => 'Akkaunt yuborildi. Admin tekshiruvi kutilmoqda.',
            'account_id' => $result['account']->id,
        ], 201);
    }

    public function update(Request $request, Account $account): JsonResponse
    {
        $request->validate(['telegram_id' => ['required', 'integer']]);

        $user = User::where('telegram_id', $request->integer('telegram_id'))->first();
        if (!$user || $account->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Ruxsat yo\'q'], 403);
        }

        $data = $request->validate([
            'price'              => ['required', 'numeric', 'min:1000'],
            'heroes_count'       => ['required', 'integer', 'min:0', 'max:131'],
            'skins_count'        => ['required', 'integer', 'min:0', 'max:1070'],
            'collection_level'   => ['required', 'string', 'max:100'],
            'description'        => ['nullable', 'string', 'max:2000'],
            'ready_for_transfer' => ['sometimes', 'boolean'],
        ]);

        $account->update($data);

        return response()->json([
            'success' => true,
            'account' => [
                'id'               => $account->id,
                'price'            => (float) $account->price,
                'heroes_count'     => $account->heroes_count,
                'skins_count'      => $account->skins_count,
                'collection_level' => $account->collection_level,
                'description'      => $account->description,
                'ready_for_transfer' => $account->ready_for_transfer,
                'status'           => $account->status,
            ],
        ]);
    }

    public function destroy(Request $request, Account $account): JsonResponse
    {
        $request->validate(['telegram_id' => ['required', 'integer']]);

        $user = User::where('telegram_id', $request->integer('telegram_id'))->first();
        if (!$user || $account->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Ruxsat yo\'q'], 403);
        }

        // Media fayllarni o'chirish
        foreach ($account->media as $media) {
            Storage::disk('public')->delete($media->file_id);
        }
        $account->delete();

        return response()->json(['success' => true]);
    }

    public function markSold(Request $request, Account $account): JsonResponse
    {
        $request->validate(['telegram_id' => ['required', 'integer']]);

        $user = User::where('telegram_id', $request->integer('telegram_id'))->first();
        if (!$user || $account->user_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Ruxsat yo\'q'], 403);
        }

        $account->update(['status' => 'sold']);

        return response()->json(['success' => true]);
    }

    public function viewAccount(Account $account): JsonResponse
    {
        $account->increment('views');

        return response()->json(['views' => $account->views]);
    }
}
