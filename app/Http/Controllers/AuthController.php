<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\JwtHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register new user
     * POST /api/auth/register
     */
    public function register(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:30|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:100',
            'role' => 'nullable|in:member,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide all required fields',
                'errors' => $validator->errors(),
            ], 400);
        }

        try {
            // Create user (password will be hashed automatically)
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => $request->password,
                'role' => $request->role ?? 'member',
            ]);

            // Generate tokens
            $accessToken = JwtHelper::generateAccessToken($user->id, $user->role);
            $refreshToken = JwtHelper::generateRefreshToken($user->id);

            // Save refresh token
            $user->update(['refreshToken' => $refreshToken]);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => $user->toSafeObject(),
                'accessToken' => $accessToken,
                'refreshToken' => $refreshToken,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error registering user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login user
     * POST /api/auth/login
     */
    public function login(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide username and password',
            ], 400);
        }

        try {
            // Find user
            $user = User::where('username', $request->username)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            // Check if user is active
            if (!$user->isActive) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated',
                ], 403);
            }

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials',
                ], 401);
            }

            // Generate tokens
            $accessToken = JwtHelper::generateAccessToken($user->id, $user->role);
            $refreshToken = JwtHelper::generateRefreshToken($user->id);

            // Update last login and save refresh token
            $user->update([
                'lastLogin' => now(),
                'refreshToken' => $refreshToken,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => $user->toSafeObject(),
                'accessToken' => $accessToken,
                'refreshToken' => $refreshToken,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error logging in',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Logout user
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->input('auth_user');
            
            // Clear refresh token
            $user->update(['refreshToken' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Logout successful',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error logging out',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Refresh access token
     * POST /api/auth/refresh
     */
    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refreshToken' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token required',
            ], 400);
        }

        try {
            // Verify refresh token
            $decoded = JwtHelper::verifyToken($request->refreshToken);

            if (!$decoded) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid refresh token',
                ], 401);
            }

            // Find user
            $user = User::find($decoded->userId);

            if (!$user || $user->refreshToken !== $request->refreshToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid refresh token',
                ], 401);
            }

            if (!$user->isActive) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your account has been deactivated',
                ], 403);
            }

            // Generate new access token
            $newAccessToken = JwtHelper::generateAccessToken($user->id, $user->role);

            return response()->json([
                'success' => true,
                'accessToken' => $newAccessToken,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error refreshing token',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current user
     * GET /api/auth/me
     */
    public function getMe(Request $request)
    {
        try {
            $user = $request->input('auth_user');

            return response()->json([
                'success' => true,
                'user' => $user->toSafeObject(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error getting user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update password
     * PUT /api/auth/password
     */
    public function updatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currentPassword' => 'required|string',
            'newPassword' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide current and new password',
            ], 400);
        }

        try {
            $user = $request->input('auth_user');

            // Verify current password
            if (!Hash::check($request->currentPassword, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                ], 401);
            }

            // Update password (will be hashed automatically)
            $user->update(['password' => $request->newPassword]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating password',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

