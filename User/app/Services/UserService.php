<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserService
{
    // Инкрементация поля created_quizzes_counter в User ($request)
    public function incrementCreatedQuizzesCounter(Request $request): JsonResponse
    {
        $request->validate([
            'login' => 'required|string',
        ]);

        $login = $request->input('login');
        $user = User::where('login', $login)->first();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $user->increment('created_quizzes_counter');

        return response()->json([
            'user' => $user,
            'message' => 'Счетчик созданных викторин успешно обновлен',
        ]);
    }

    // Инкрементация поля created_quizzes_counter в User ($login)
    public function incrementCreatedQuizzesCounter_notRequest(string $login): void
    {
        $user = User::where('login', $login)->first();

        if (!$user) {
            throw new \Exception('Пользователь не найден');
        }

        $user->increment('created_quizzes_counter');
    }
}
