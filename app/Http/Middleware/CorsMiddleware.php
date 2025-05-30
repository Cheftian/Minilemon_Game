<?php

namespace App\Http\Middleware;

use Closure;

class CorsMiddleware
{
    // Ubah ini ke true jika ingin izinkan semua origin
    private $ALLOW_ALL_ORIGINS = true;

    private $allowedOrigins = [
        'http://localhost:3000',
    ];

    public function handle($request, Closure $next)
    {
        $origin = $request->headers->get('Origin');

        // Jika method OPTIONS (preflight), tanggapi segera
        if ($request->getMethod() === "OPTIONS") {
            if ($this->ALLOW_ALL_ORIGINS || in_array($origin, $this->allowedOrigins)) {
                return response('', 200)->withHeaders([
                    'Access-Control-Allow-Origin' => $this->ALLOW_ALL_ORIGINS ? '*' : $origin,
                    'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
                    'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
                ]);
            } else {
                return response('Forbidden', 403);
            }
        }

        $response = $next($request);

        if ($this->ALLOW_ALL_ORIGINS || in_array($origin, $this->allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $this->ALLOW_ALL_ORIGINS ? '*' : $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        return $response;
    }
}
