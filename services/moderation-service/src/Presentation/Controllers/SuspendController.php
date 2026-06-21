<?php

namespace App\Presentation\Controllers;

use App\Business\Services\ModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SuspendController extends BaseController
{
    public function __construct(private ModerationService $moderationService) {}

    public function suspend(Request $request, int $id): JsonResponse
    {
        try {
            $result = $this->moderationService->suspendKorisnikDirect($id, $request->user(), 3);

            return response()->json([
                'success' => true,
                'message' => 'Korisnik suspendovan na 3 dana',
                'data' => $result['korisnik'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], is_int($c = $e->getCode()) && $c >= 100 && $c < 600 ? $c : 500);
        }
    }
}
