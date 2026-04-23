<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request, int $telegramId): View
    {
        $user = User::where('telegram_id', $telegramId)->firstOrFail();

        // Faol e'lonlar + media
        $accounts = Account::with(['media'])
            ->where('user_id', $user->id)
            ->active()
            ->latest()
            ->get();

        // Ko'ruvchining telegram_id si (webapp dan ?viewer_id=xxx orqali)
        $viewerId = $request->integer('viewer_id');
        $isOwner  = $viewerId && $viewerId === $telegramId;

        $botUsername = ltrim(config('services.telegram.bot_username', ''), '@');

        return view('webapp.profile', compact('user', 'accounts', 'isOwner', 'botUsername'));
    }
}
