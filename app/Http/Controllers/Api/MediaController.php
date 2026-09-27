<?php

namespace App\Http\Controllers\Api;

use App\Services\MediaService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{
    private MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'iRTM Media Service',
            'version' => '0.1.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function helmetUrl(int $memberId)
    {

        return response(
            $this->mediaService->helmetUrl($memberId),
            200
        )->header('Content-Type', 'text/plain');
    }

}
