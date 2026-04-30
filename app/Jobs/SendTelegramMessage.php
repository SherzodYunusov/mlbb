<?php

namespace App\Jobs;

use App\Services\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTelegramMessage implements ShouldQueue
{
    use Queueable, InteractsWithQueue;

    public int $tries  = 3;
    public int $backoff = 10;

    public function __construct(
        public readonly int|string $chatId,
        public readonly string     $text,
        public readonly array      $keyboard = [],
        public readonly ?int       $threadId = null,
    ) {}

    public function handle(TelegramService $telegram): void
    {
        $telegram->sendMessage($this->chatId, $this->text, $this->keyboard, $this->threadId);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendTelegramMessage failed', [
            'chat_id' => $this->chatId,
            'error'   => $e->getMessage(),
        ]);
    }
}
