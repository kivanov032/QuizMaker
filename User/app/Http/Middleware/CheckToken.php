<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class CheckToken
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // Если пользователь не найден (токен истёк)
        if (!$user) {
            $token = $request->bearerToken();
            if ($token) {
                // Найти токен и удалить его
                $tokenRecord = PersonalAccessToken::findToken($token);
                $tokenRecord?->delete();
            }
            return response()->json(['message' => 'Токен истёк или недействителен'], 401);
        }

        return $next($request);
    }
}
