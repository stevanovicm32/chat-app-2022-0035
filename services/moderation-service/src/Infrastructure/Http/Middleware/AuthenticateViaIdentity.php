<?php

namespace App\Infrastructure\Http\Middleware;

use App\Infrastructure\Clients\IdentityClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateViaIdentity
{
    public function __construct(private IdentityClient $identityClient) {}

    public function handle(Request $request, Closure $next): Response
    {
        $auth = $request->header('Authorization');
        if (!$auth) {
            return response()->json(['success' => false, 'message' => 'Niste autentifikovani'], 401);
        }

        $user = $this->identityClient->getUserFromToken($auth);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Neispravan token'], 401);
        }

        $request->attributes->set('identity_user', $user);
        $request->setUserResolver(fn () => (object) $user);

        return $next($request);
    }
}
