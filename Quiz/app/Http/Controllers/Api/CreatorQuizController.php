<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateQuizRequest;
use App\Services\CreatorQuizService;
use Illuminate\Http\Request;

class CreatorQuizController extends Controller
{
    private CreatorQuizService $creatorQuizService;

    public function __construct(CreatorQuizService $creatorQuizService)
    {
        $this->creatorQuizService = $creatorQuizService;
    }

    public function checkActivity(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->creatorQuizService->checkActivity();
    }

    public function searchQuizErrors(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->creatorQuizService->searchQuizErrors($request);
    }

    public function fixQuizErrors(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->creatorQuizService->fixQuizErrors($request);
    }

    public function createQuiz(CreateQuizRequest $request): \Illuminate\Http\JsonResponse
    {
        return $this->creatorQuizService->createQuiz($request);
    }
}

