<?php

namespace App\Presentation\Controllers;

use Illuminate\Http\JsonResponse;

class HealthController extends BaseController
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'moderation-service',
            'kafka' => extension_loaded('rdkafka') ? 'enabled' : 'disabled',
        ]);
    }
}
