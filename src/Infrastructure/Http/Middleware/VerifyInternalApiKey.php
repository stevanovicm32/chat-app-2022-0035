<?php

namespace App\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyInternalApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-Internal-Api-Key');
        $expected = config('app.internal_api_key', env('INTERNAL_API_KEY'));

        if (!$expected || $key !== $expected) {
            return response()->json([
                'success' => false,
                'message' => 'Nedozvoljen interni pristup',
            ], 403);
        }

        return $next($request);
    }
}
