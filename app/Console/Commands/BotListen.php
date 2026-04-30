<?php

namespace App\Console\Commands;

use App\Http\Controllers\WebhookController;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BotListen extends Command
{
    protected $signature   = 'bot:listen';
    protected $description = 'Telegram bot callback tugmalarini (approve/reject) tinglaydi';

    public function handle(TelegramService $telegram, WebhookController $webhook): void
    {
        // ── 1. Webhookni o'chiramiz (getUpdates bilan conflict bo'lmasligi uchun) ──
        $this->deleteWebhook();

        $this->info('🤖 Bot ishga tushdi — callback\'larni tinglayapman...');
        $this->info('To\'xtatish uchun Ctrl+C bosing.');
        $this->newLine();

        // ── 2. Offsetni cache dan olamiz ──
        $offset = (int) Cache::get('bot_listen_offset', 0);
        $this->line("<fg=gray>Offset: {$offset}</>");
        $this->newLine();

        while (true) {
            try {
                // ── 3. Yangi updatelarni so'raymiz ──
                $this->line("<fg=gray>[" . now()->format('H:i:s') . "] getUpdates (offset={$offset})...</>");

                $updates = $telegram->getUpdates($offset, timeout: 25);

                // ── 4. Debug: kelgan barcha updatelarni ko'rsatamiz ──
                if (empty($updates)) {
                    $this->line('<fg=gray>  → Update yo\'q, kutilmoqda...</>');
                } else {
                    $this->info('  → ' . count($updates) . ' ta update keldi');
                }

                foreach ($updates as $update) {
                    $updateId = $update['update_id'];

                    // Raw update ni ko'rsatamiz (debug uchun)
                    $type = match(true) {
                        isset($update['callback_query']) => 'callback_query',
                        isset($update['message'])        => 'message',
                        default                          => 'other',
                    };

                    $data = $update['callback_query']['data']
                         ?? $update['message']['text']
                         ?? '(empty)';

                    $this->line(
                        "  [<fg=yellow>#{$updateId}</>] "
                        . "<fg=cyan>{$type}</> "
                        . "→ <fg=white>{$data}</>"
                    );

                    // ── 5. Handlega uzatamiz — hammasi WebhookController orqali ──
                    $webhook->handleUpdate($update);

                    // ── 6. Offsetni yangilaymiz ──
                    $offset = $updateId + 1;
                    Cache::put('bot_listen_offset', $offset, now()->addDays(7));
                }

            } catch (\Throwable $e) {
                $this->error('❌ Xato: ' . $e->getMessage());
                $this->line('<fg=gray>5 soniyadan keyin qayta urinadi...</>');
                sleep(5);
            }
        }
    }

    private function deleteWebhook(): void
    {
        $token = config('services.telegram.bot_token');
        $url   = "https://api.telegram.org/bot{$token}/deleteWebhook";

        $response = Http::post($url, ['drop_pending_updates' => false]);

        if ($response->successful() && $response->json('ok')) {
            $this->info('🔗 Webhook o\'chirildi — getUpdates rejimiga o\'tildi');
        } else {
            $this->warn('⚠️  Webhookni o\'chirishda muammo: ' . $response->body());
        }
    }
}
