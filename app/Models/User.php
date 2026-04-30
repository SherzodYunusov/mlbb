<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasFactory;

    protected $fillable = [
        'telegram_id',
        'username',
        'first_name',
        'role',
        'balance',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'telegram_id' => 'integer',
            'balance'     => 'decimal:2',
            'is_active'   => 'boolean',
        ];
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'sender_id');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGarant(): bool
    {
        return $this->role === 'garant';
    }
}
