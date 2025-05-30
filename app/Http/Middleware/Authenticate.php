<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Firebase\JWT\ExpiredException;

class Authenticate
{
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken(); // Ambil dari Authorization: Bearer <token>

        if (!$token) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $decoded = JWT::decode($token, new Key(env('JWT_SECRET'), 'HS256'));
            $user = User::find($decoded->sub);
        
            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
        
            $request->setUserResolver(fn() => $user);
        
        } catch (ExpiredException $e) {
            return response()->json(['message' => 'Token expired'], 401);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token', 'error' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}
