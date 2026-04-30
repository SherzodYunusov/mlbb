<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'price',
        'heroes_count',
        'skins_count',
        'collection_level',
        'description',
        'ready_for_transfer',
        'status',
        'video_size',
        'channel_message_id',
        'last_confirmed_at',
        'checkin_sent_at',
        'views',
    ];

    protected function casts(): array
    {
        return [
            'price'              => 'decimal:2',
            'heroes_count'       => 'integer',
            'skins_count'        => 'integer',
            'ready_for_transfer' => 'boolean',
            'last_confirmed_at'  => 'datetime',
            'checkin_sent_at'    => 'datetime',
        ];
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(AccountMedia::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }
}
