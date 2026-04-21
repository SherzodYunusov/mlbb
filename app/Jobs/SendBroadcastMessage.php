<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBroadcastMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Job muvaffaqiyatsiz bo'lsa — qayta urinishlar soni
    public int $tries = 1;

    // Job bajarilishi uchun max vaqt (sekund)
    public int $timeout = 3600;

    public function __construct(
        private readonly string $message,
        private readonly int    $adminId,
    ) {}

    public function handle(TelegramService $telegram): void
    {
        $broadcastText = "📢 <b>MLBB Market</b>\n\n{$this->message}";
        $sent   = 0;
        $failed = 0;

        // chunk(100) — xotiraga 100 ta user birdan yuklanadi, NOT all()
        User::where('is_active', true)
            ->where('telegram_id', '!=', $this->adminId)
            ->chunk(100, function ($users) use ($telegram, $broadcastText, &$sent, &$failed) {
                foreach ($users as $user) {
                    try {
                        $result = $telegram->sendMessage($user->telegram_id, $broadcastText);

                        if ($result) {
                            $sent++;
                        } else {
                            $failed++;
                        }
                    } catch (\Throwable $e) {
                        $errMsg = $e->getMessage();

                        // Bot bloklangan yoki chat topilmadi → foydalanuvchini o'chir
                        if (
                            str_contains($errMsg, 'bot was blocked') ||
                            str_contains($errMsg, 'chat not found') ||
                            str_contains($errMsg, 'user is deactivated') ||
                            str_contains($errMsg, '403')
                        ) {
                            $user->update(['is_active' => false]);
                            Log::info("Broadcast: user {$user->telegram_id} deactivated (blocked/not found)");
                        } else {
                            Log::warning("Broadcast: failed to send to {$user->telegram_id}", ['error' => $errMsg]);
                        }

                        $failed++;
                    }

                    // Anti-spam: 20 msg/sec limitidan xavfsiz
                    usleep(50_000); // 50ms
                }
            });

        // Adminga yakuniy hisobot
        $telegram->sendMessage(
            $this->adminId,
            "📊 <b>Broadcast yakunlandi!</b>\n\n"
            . "✅ Yuborildi: <b>{$sent}</b> ta\n"
            . ($failed ? "❌ Xato/bloklangan: <b>{$failed}</b> ta\n" : "")
            . "\n📝 <i>{$this->message}</i>"
        );
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendBroadcastMessage job failed', ['error' => $e->getMessage()]);

        // Adminga xato haqida xabar
        try {
            $telegram = app(TelegramService::class);
            $telegram->sendMessage(
                $this->adminId,
                "❌ <b>Broadcast xato bilan to'xtadi!</b>\n\n"
                . "<code>{$e->getMessage()}</code>"
            );
        } catch (\Throwable) {}
    }
}
