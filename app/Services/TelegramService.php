<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $baseUrl;
    public string|int $adminChannelId;
    public int $adminId;
    public string|int|null $dealsGroupId;

    public function __construct()
    {
        $token = config('services.telegram.bot_token');
        $this->baseUrl      = "https://api.telegram.org/bot{$token}";
        $this->adminChannelId = config('services.telegram.admin_channel_id');
        $this->adminId      = (int) config('services.telegram.admin_id');
        $this->dealsGroupId = config('services.telegram.deals_group_id') ?: null;
    }

    // ══════════════════════════════════════════════
    //  SEND
    // ══════════════════════════════════════════════

    public function sendMessage(
        string|int $chatId,
        string $text,
        array $inlineKeyboard = [],
        ?int $threadId = null
    ): ?int {
        $payload = [
            'chat_id'    => $chatId,
            'text'       => $text,
            'parse_mode' => 'HTML',
        ];

        if (!empty($inlineKeyboard)) {
            $payload['reply_markup'] = json_encode(['inline_keyboard' => $inlineKeyboard]);
        }

        if ($threadId) {
            $payload['message_thread_id'] = $threadId;
        }

        $response = Http::post("{$this->baseUrl}/sendMessage", $payload);

        if (!$response->successful()) {
            Log::error('Telegram sendMessage failed', ['body' => $response->json()]);
            return null;
        }

        return $response->json('result.message_id');
    }

    public function sendToAdmin(string $caption, array $absoluteFilePaths, array $inlineKeyboard): ?int
    {
        $existing = array_values(array_filter($absoluteFilePaths, 'file_exists'));

        // Rasmlar va video'larni ajratamiz
        $photos = array_values(array_filter($existing, fn($p) => !$this->isVideo($p)));
        $videos = array_values(array_filter($existing, fn($p) =>  $this->isVideo($p)));

        if (count($photos) === 1 && empty($videos)) {
            // 1 ta rasm → caption + keyboard birgalikda
            return $this->sendSingleFile($this->adminChannelId, $photos[0], $caption, $inlineKeyboard);
        }

        if (empty($photos) && count($videos) === 1) {
            // Faqat 1 ta video → caption + keyboard birgalikda
            return $this->sendSingleFile($this->adminChannelId, $videos[0], $caption, $inlineKeyboard);
        }

        // Bir nechta rasm → media group (caption birinchi elementda)
        if (!empty($photos)) {
            $this->sendMediaGroup($this->adminChannelId, $photos, $caption);
        }

        // Video(lar) alohida
        foreach ($videos as $video) {
            $this->sendSingleFile($this->adminChannelId, $video, '');
        }

        // Fayl bo'lmasa yoki media group bo'lsa — tugmalar alohida xabar
        if (empty($existing)) {
            return $this->sendMessage($this->adminChannelId, $caption, $inlineKeyboard);
        }

        return $this->sendMessage($this->adminChannelId, '👆 Yuqoridagi e\'lon', $inlineKeyboard);
    }

    // ══════════════════════════════════════════════
    //  EDIT
    // ══════════════════════════════════════════════

    public function editMessageText(
        string|int $chatId,
        int $messageId,
        string $newText,
        array $inlineKeyboard = []
    ): void {
        $response = Http::post("{$this->baseUrl}/editMessageText", [
            'chat_id'      => $chatId,
            'message_id'   => $messageId,
            'text'         => $newText,
            'parse_mode'   => 'HTML',
            'reply_markup' => json_encode(['inline_keyboard' => $inlineKeyboard]),
        ]);

        if (!$response->successful()) {
            Log::error('Telegram editMessageText failed', ['body' => $response->json()]);
        }
    }

    public function editMessageReplyMarkup(string|int $chatId, int $messageId, array $inlineKeyboard = []): void
    {
        $response = Http::post("{$this->baseUrl}/editMessageReplyMarkup", [
            'chat_id'      => $chatId,
            'message_id'   => $messageId,
            'reply_markup' => json_encode(['inline_keyboard' => $inlineKeyboard]),
        ]);

        if (!$response->successful()) {
            Log::error('Telegram editMessageReplyMarkup failed', ['body' => $response->json()]);
        }
    }

    // ══════════════════════════════════════════════
    //  GROUP MANAGEMENT
    // ══════════════════════════════════════════════

    /**
     * Forum topic yaratadi (deals guruhida).
     * Guruh "Topics" rejimida bo'lishi va bot admin bo'lishi shart.
     */
    public function createForumTopic(string|int $chatId, string $name): ?int
    {
        $response = Http::post("{$this->baseUrl}/createForumTopic", [
            'chat_id'    => $chatId,
            'name'       => $name,
            'icon_color' => 0x6FB9F0,
        ]);

        if (!$response->successful()) {
            Log::error('createForumTopic failed', ['body' => $response->json()]);
            return null;
        }

        return $response->json('result.message_thread_id');
    }

    /**
     * Foydalanuvchini guruhga taklif qilish uchun invite link yaratadi.
     * (Bot API foydalanuvchini to'g'ridan-to'g'ri qo'sha olmaydi — privacy cheklovi)
     */
    public function createInviteLink(string|int $chatId, string $name = '', ?int $threadId = null): ?string
    {
        $payload = [
            'chat_id'      => $chatId,
            'name'         => $name,
            'member_limit' => 1,       // faqat bir kishi ishlatsin
            'expire_date'  => now()->addHours(24)->timestamp,
        ];

        $response = Http::post("{$this->baseUrl}/createChatInviteLink", $payload);

        if (!$response->successful()) {
            Log::error('createChatInviteLink failed', ['body' => $response->json()]);
            return null;
        }

        return $response->json('result.invite_link');
    }

    /**
     * Foydalanuvchini guruhda admin qiladi.
     */
    public function promoteChatMember(string|int $chatId, int $userId): bool
    {
        $response = Http::post("{$this->baseUrl}/promoteChatMember", [
            'chat_id'              => $chatId,
            'user_id'              => $userId,
            'can_manage_chat'      => true,
            'can_delete_messages'  => true,
            'can_restrict_members' => true,
            'can_promote_members'  => false,
            'can_change_info'      => false,
            'can_invite_users'     => true,
            'can_pin_messages'     => true,
        ]);

        $ok = $response->json('result') === true;

        if (!$ok) {
            Log::error('promoteChatMember failed', ['body' => $response->json()]);
        }

        return $ok;
    }

    // ══════════════════════════════════════════════
    //  BOT SETUP
    // ══════════════════════════════════════════════

    /**
     * Bot buyruqlar menyusini o'rnatadi.
     * $scope bo'sh bo'lsa — barcha chatlar uchun.
     * $scope = ['type'=>'chat','chat_id'=>123] → faqat shu chat uchun.
     */
    public function setMyCommands(array $commands, array $scope = []): bool
    {
        $payload = ['commands' => json_encode($commands)];
        if (!empty($scope)) {
            $payload['scope'] = json_encode($scope);
        }
        $response = Http::post("{$this->baseUrl}/setMyCommands", $payload);
        return $response->json('result') === true;
    }

    // ══════════════════════════════════════════════
    //  CALLBACK
    // ══════════════════════════════════════════════

    public function answerCallbackQuery(string $callbackQueryId, string $text = '', bool $showAlert = false): void
    {
        Http::post("{$this->baseUrl}/answerCallbackQuery", [
            'callback_query_id' => $callbackQueryId,
            'text'              => $text,
            'show_alert'        => $showAlert,
        ]);
    }

    // ══════════════════════════════════════════════
    //  POLLING
    // ══════════════════════════════════════════════

    public function getUpdates(int $offset = 0, int $timeout = 30): array
    {
        $response = Http::timeout($timeout + 5)->get("{$this->baseUrl}/getUpdates", [
            'offset'          => $offset,
            'timeout'         => $timeout,
            'allowed_updates' => json_encode(['message', 'callback_query']),
        ]);

        if (!$response->successful()) {
            $body = $response->json();
            Log::error('Telegram getUpdates failed', ['body' => $body]);
            // 409 Conflict: boshqa jarayon ham polling qilyapti
            throw new \RuntimeException(
                'getUpdates error ' . $response->status() . ': ' . ($body['description'] ?? 'unknown')
            );
        }

        return $response->json('result', []);
    }

    // ══════════════════════════════════════════════
    //  PRIVATE helpers
    // ══════════════════════════════════════════════

    private function isVideo(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['mp4', 'mov', 'avi', 'webm', 'qt']);
    }

    /**
     * Bitta rasm yoki video yuboradi.
     * fopen() bilan stream qiladi — file_get_contents() dan 3-5x tez.
     * Caption va keyboard bir xabarda yuboriladi.
     * @return int|null  yuborilgan message_id
     */
    private function sendSingleFile(
        string|int $chatId,
        string     $filePath,
        string     $caption    = '',
        array      $keyboard   = [],
    ): ?int {
        $isVideo  = $this->isVideo($filePath);
        $endpoint = $isVideo ? 'sendVideo' : 'sendPhoto';
        $field    = $isVideo ? 'video'     : 'photo';

        // fopen → stream upload (xotiraga butun fayl o'qilmaydi)
        $stream = @fopen($filePath, 'r');
        if (!$stream) {
            Log::error("sendSingleFile: fayl topilmadi: {$filePath}");
            return null;
        }
        $request = Http::timeout(120)->asMultipart()
            ->attach($field, $stream, basename($filePath));

        $payload = ['chat_id' => $chatId];

        if ($caption) {
            $payload['caption']    = $caption;
            $payload['parse_mode'] = 'HTML';
        }

        if (!empty($keyboard)) {
            $payload['reply_markup'] = json_encode(['inline_keyboard' => $keyboard]);
        }

        $response = $request->post("{$this->baseUrl}/{$endpoint}", $payload);

        if (is_resource($stream)) {
            fclose($stream);
        }

        if (!$response->successful()) {
            Log::error("Telegram {$endpoint} failed", [
                'status' => $response->status(),
                'body'   => $response->json(),
                'file'   => $filePath,
            ]);
            return null;
        }

        return $response->json('result.message_id');
    }

    /**
     * Bir nechta rasmni album (media group) sifatida yuboradi.
     * Caption birinchi elementda ko'rsatiladi.
     */
    private function sendMediaGroup(string|int $chatId, array $filePaths, string $caption = ''): void
    {
        $request    = Http::timeout(180)->asMultipart();
        $mediaItems = [];

        foreach ($filePaths as $index => $path) {
            $key     = "file_{$index}";
            $isVideo = $this->isVideo($path);
            $stream  = @fopen($path, 'r');

            if (!$stream) {
                Log::error("sendMediaGroup: fayl topilmadi: {$path}");
                continue;
            }

            $request = $request->attach($key, $stream, basename($path));

            $item = ['type' => $isVideo ? 'video' : 'photo', 'media' => "attach://{$key}"];

            // Caption faqat birinchi elementda
            if ($index === 0 && $caption) {
                $item['caption']    = $caption;
                $item['parse_mode'] = 'HTML';
            }

            $mediaItems[] = $item;
        }

        $response = $request->post("{$this->baseUrl}/sendMediaGroup", [
            'chat_id' => $chatId,
            'media'   => json_encode($mediaItems),
        ]);

        if (!$response->successful()) {
            Log::error('Telegram sendMediaGroup failed', [
                'status' => $response->status(),
                'body'   => $response->json(),
                'files'  => $filePaths,
            ]);
        }
    }
}
