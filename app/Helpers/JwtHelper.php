<?php

namespace App\Helpers;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtHelper
{
    /**
     * Generate JWT access token
     *
     * @param int $userId
     * @param string $role
     * @return string
     */
    public static function generateAccessToken(int $userId, string $role): string
    {
        $secret = config('jwt.secret');
        $expireMinutes = config('jwt.access_token_expire');
        
        $payload = [
            'userId' => $userId,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + ($expireMinutes * 60),
        ];
        
        return JWT::encode($payload, $secret, config('jwt.algorithm'));
    }

    /**
     * Generate JWT refresh token
     *
     * @param int $userId
     * @return string
     */
    public static function generateRefreshToken(int $userId): string
    {
        $secret = config('jwt.secret');
        $expireMinutes = config('jwt.refresh_token_expire');
        
        $payload = [
            'userId' => $userId,
            'iat' => time(),
            'exp' => time() + ($expireMinutes * 60),
        ];
        
        return JWT::encode($payload, $secret, config('jwt.algorithm'));
    }

    /**
     * Verify and decode JWT token
     *
     * @param string $token
     * @return object|null
     */
    public static function verifyToken(string $token): ?object
    {
        try {
            $secret = config('jwt.secret');
            $decoded = JWT::decode($token, new Key($secret, config('jwt.algorithm')));
            return $decoded;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Extract token from Authorization header
     *
     * @param string|null $authHeader
     * @return string|null
     */
    public static function extractTokenFromHeader(?string $authHeader): ?string
    {
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return null;
        }
        
        return substr($authHeader, 7);
    }
}

