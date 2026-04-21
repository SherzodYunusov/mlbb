<?php

namespace App\Jobs;

use App\Models\Account;
use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NotifyAdminJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries  = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly Account $account,
        public readonly array $storagePaths,
    ) {}

    public function handle(TelegramService $telegram): void
    {
        $account = $this->account->load('user');

        $caption = $this->buildCaption($account);

        // Storage::disk('public') — fayllar public diskda saqlanadi
        $absolutePaths = array_map(
            fn(string $p) => Storage::disk('public')->path($p),
            $this->storagePaths,
        );

        // Debug: qaysi fayllar topildi/topilmadi
        foreach ($absolutePaths as $path) {
            Log::info('NotifyAdminJob file check', [
                'path'   => $path,
                'exists' => file_exists($path),
            ]);
        }

        // Thumbnail path larini olib tashlaymiz — faqat asl rasmlar yuborilsin
        $absolutePaths = array_values(array_filter(
            $absolutePaths,
            fn(string $p) => file_exists($p) && !str_contains($p, 'thumb_')
        ));

        $keyboard = [
            [
                ['text' => '✅ Tasdiqlash', 'callback_data' => "approve_{$account->id}"],
                ['text' => '❌ Rad etish',  'callback_data' => "reject_{$account->id}"],
            ],
        ];

        $messageId = $telegram->sendToAdmin($caption, $absolutePaths, $keyboard);

        if ($messageId) {
            $account->update(['channel_message_id' => $messageId]);
        }
    }

    private function buildCaption(Account $account): string
    {
        $seller   = $account->user->username
            ? "@{$account->user->username}"
            : $account->user->first_name;
        $transfer = $account->ready_for_transfer ? '✅ Ha' : '❌ Yo\'q';

        $lines = [
            '🎮 <b>Yangi akkaunt sotuvga qo\'yildi!</b>',
            '',
            "💰 <b>Narx:</b> " . number_format($account->price, 0, '.', ' ') . " so'm",
            "⚔️ <b>Qahramonlar:</b> {$account->heroes_count} ta",
            "👗 <b>Skinlar:</b> {$account->skins_count} ta",
            "🏆 <b>Kolleksiya:</b> {$account->collection_level}",
            "🔄 <b>Transfer:</b> {$transfer}",
        ];

        if (!empty($account->description)) {
            $lines[] = '';
            $lines[] = "📝 <b>Tavsif:</b> {$account->description}";
        }

        $lines[] = '';
        $lines[] = "👤 <b>Sotuvchi:</b> {$seller}";
        $lines[] = "🆔 <b>ID:</b> #{$account->id}";

        return implode("\n", $lines);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('NotifyAdminJob failed', [
            'account_id' => $this->account->id,
            'error'      => $e->getMessage(),
        ]);
    }
}
