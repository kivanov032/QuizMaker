<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserService
{
    public function incrementCreatedQuizzesCounter(Request $request): JsonResponse
    {
        $request->validate([
            'id_user' => 'required|uuid',
        ]);

        $id_user = $request->input('id_user');
        $user = User::where('id_user', $id_user)->first();

        if (!$user) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $user->increment('created_quizzes_counter');

        return response()->json([
            'user' => $user,
            'message' => 'Счетчик созданных викторин успешно обновлен',
        ]);
    }
}
