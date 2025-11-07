<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\JwtHelper;
use App\Models\User;

class JwtAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get token from header
        $authHeader = $request->header('Authorization');
        $token = JwtHelper::extractTokenFromHeader($authHeader);
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required. Please log in.',
            ], 401);
        }
        
        // Verify token
        $decoded = JwtHelper::verifyToken($token);
        
        if (!$decoded) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token. Please log in again.',
            ], 401);
        }
        
        // Get user from database
        $user = User::find($decoded->userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found. Please log in again.',
            ], 401);
        }
        
        if (!$user->isActive) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact administrator.',
            ], 403);
        }
        
        // Attach user to request
        $request->merge([
            'auth_user' => $user,
            'user_id' => $user->id,
            'user_role' => $user->role,
        ]);
        
        return $next($request);
    }
}

