<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $request->validate(['token' => ['required', 'string']]);

        $record = LoginToken::with('user')
            ->where('token', $request->input('token'))
            ->first();

        if (!$record) {
            return response()->json(['error' => 'Token topilmadi'], 404);
        }

        if ($record->isExpired()) {
            $record->delete();
            return response()->json(['error' => 'Token muddati tugagan. Botdan qayta kiring.'], 401);
        }

        $user = $record->user;

        // Token o'chirilmaydi — 24 soat davomida qayta ishlatsa bo'ladi

        return response()->json([
            'tg_id'      => $user->telegram_id,
            'username'   => $user->username,
            'first_name' => $user->first_name,
        ]);
    }
}
