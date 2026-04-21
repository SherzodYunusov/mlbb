<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'sender_id',
        'parent_id',
        'message',
        'is_hidden',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'is_hidden' => 'boolean',
            'edited_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id')
                    ->where('is_hidden', false)
                    ->with('sender')
                    ->oldest();
    }

    /** Spam tekshiruvi */
    public static function isSpam(string $text): bool
    {
        // Telefon raqami (7+ raqam)
        if (preg_match('/\+?[\d][\d\s\-\(\)]{7,}[\d]/', $text)) return true;

        // @mention
        if (preg_match('/@[a-zA-Z0-9_]{3,}/', $text)) return true;

        // Tashqi havola
        if (preg_match('/t\.me\/|wa\.me\/|http[s]?:\/\//i', $text)) return true;

        // Kalit so'zlar
        $keywords = [
            'telegram', 'lichka', 'nomeringni', 'raqamingni',
            'whatsapp', 'signal', 'instagram', 'tashqarida',
            'shaxsiy yoz', 'dm ber', 'личка',
        ];

        $lower = mb_strtolower($text);
        foreach ($keywords as $kw) {
            if (str_contains($lower, $kw)) return true;
        }

        return false;
    }

    /** Tahrirlash mumkinmi? (15 daqiqa ichida) */
    public function canEdit(): bool
    {
        return $this->created_at->diffInMinutes(now()) <= 15;
    }
}
