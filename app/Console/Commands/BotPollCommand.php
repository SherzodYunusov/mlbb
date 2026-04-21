<?php

namespace App\Console\Commands;

use App\Http\Controllers\WebhookController;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BotPollCommand extends Command
{
    protected $signature   = 'bot:poll';
    protected $description = 'Telegram botni polling orqali ishga tushirish (local test uchun)';

    private int $offset = 0;

    public function handle(TelegramService $telegram, WebhookController $webhook): int
    {
        // Avval webhookni o'chiramiz (polling bilan bir vaqtda ishlamaydi)
        $token = config('services.telegram.bot_token');
        Http::post("https://api.telegram.org/bot{$token}/deleteWebhook", ['drop_pending_updates' => false]);

        $this->info('🤖 Bot polling rejimida ishga tushdi. To\'xtatish: Ctrl+C');
        $this->line('─────────────────────────────────────');

        while (true) {
            try {
                $updates = $telegram->getUpdates($this->offset, timeout: 25);
            } catch (\RuntimeException $e) {
                $this->error("❌ getUpdates xato: " . $e->getMessage());
                sleep(3);
                continue;
            }

            if (empty($updates)) {
                $this->line('<fg=gray>[' . date('H:i:s') . '] polling...</>');
            }

            foreach ($updates as $update) {
                $this->offset = $update['update_id'] + 1;
                $this->logUpdate($update);

                try {
                    $webhook->handleUpdate($update);
                } catch (\Throwable $e) {
                    $this->error("❌ Xato: " . $e->getMessage());
                }
            }
        }
    }

    private function logUpdate(array $update): void
    {
        if (isset($update['message'])) {
            $from = $update['message']['from'];
            $text = $update['message']['text'] ?? '[media]';
            $name = ($from['username'] ?? null)
                ? '@' . $from['username']
                : ($from['first_name'] ?? 'Noma\'lum');

            $this->line("📨 <fg=cyan>{$name}</> → <fg=yellow>{$text}</>");
        }

        if (isset($update['callback_query'])) {
            $from = $update['callback_query']['from'];
            $data = $update['callback_query']['data'] ?? '';
            $name = ($from['username'] ?? null)
                ? '@' . $from['username']
                : ($from['first_name'] ?? 'Noma\'lum');

            $this->line("🔘 <fg=magenta>{$name}</> → <fg=green>[{$data}]</>");
        }
    }
}
