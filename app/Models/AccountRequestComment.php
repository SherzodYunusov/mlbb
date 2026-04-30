<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountRequestComment extends Model
{
    protected $fillable = ['request_id', 'sender_id', 'message'];

    public function request(): BelongsTo
    {
        return $this->belongsTo(AccountRequest::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function canEdit(): bool
    {
        return $this->created_at->diffInMinutes(now()) <= 15;
    }
}
