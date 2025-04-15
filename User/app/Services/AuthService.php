<?php

namespace App\Services;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;

class AuthService
{


    public function incrementQuizCounterForUser(string $login): void
    {
        Log::info("Incrementing quiz counter for user: {$login}");
        $user = User::where('login', $login)->first();

        if (!$user) {
            throw new \Exception('Пользователь не найден');
        }

        $user->increment('created_quizzes_counter');
    }


}
