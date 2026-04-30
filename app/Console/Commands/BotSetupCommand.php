<?php

namespace App\Console\Commands;

use App\Services\TelegramService;
use Illuminate\Console\Command;

class BotSetupCommand extends Command
{
    protected $signature   = 'bot:setup';
    protected $description = 'Telegram bot menyusi va sozlamalarini o\'rnatish';

    public function handle(TelegramService $telegram): int
    {
        $commands = [
            ['command' => 'start', 'description' => '🛒 Marketplacega kirish'],
        ];

        $ok = $telegram->setMyCommands($commands);
        $this->line($ok ? '✅ Buyruqlar o\'rnatildi' : '❌ Buyruqlar o\'rnatishda XATO');

        $this->info('');
        $this->info('🤖 Bot sozlash yakunlandi!');

        return Command::SUCCESS;
    }
}
