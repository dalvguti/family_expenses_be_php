<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                ->header('Access-Control-Allow-Origin', $this->getAllowedOrigin($request))
                ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Access-Control-Max-Age', '86400');
        }

        $response = $next($request);
        
        $response->headers->set('Access-Control-Allow-Origin', $this->getAllowedOrigin($request));
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Expose-Headers', 'Authorization');
        
        return $response;
    }

    /**
     * Get the allowed origin for the request
     *
     * @param Request $request
     * @return string
     */
    protected function getAllowedOrigin(Request $request): string
    {
        $allowedOrigins = config('cors.allowed_origins');
        
        // If wildcard is allowed, return wildcard
        if (in_array('*', $allowedOrigins)) {
            return '*';
        }
        
        // Get the origin from request
        $origin = $request->headers->get('Origin');
        
        // Check if origin is in allowed list
        if ($origin && in_array($origin, $allowedOrigins)) {
            return $origin;
        }
        
        // Default to first allowed origin or wildcard
        return $allowedOrigins[0] ?? '*';
    }
}

