<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Secret Key
    |--------------------------------------------------------------------------
    |
    | This key is used to sign your JWT tokens. Keep this value secure!
    |
    */
    'secret' => env('JWT_SECRET', 'your-secret-key-change-this-in-production'),

    /*
    |--------------------------------------------------------------------------
    | JWT Access Token Expiration
    |--------------------------------------------------------------------------
    |
    | The time in minutes that the access token will be valid for.
    | Default: 1440 minutes (24 hours)
    |
    */
    'access_token_expire' => env('JWT_ACCESS_TOKEN_EXPIRE', 1440),

    /*
    |--------------------------------------------------------------------------
    | JWT Refresh Token Expiration
    |--------------------------------------------------------------------------
    |
    | The time in minutes that the refresh token will be valid for.
    | Default: 10080 minutes (7 days)
    |
    */
    'refresh_token_expire' => env('JWT_REFRESH_TOKEN_EXPIRE', 10080),

    /*
    |--------------------------------------------------------------------------
    | JWT Algorithm
    |--------------------------------------------------------------------------
    |
    | The algorithm used to sign the tokens.
    |
    */
    'algorithm' => 'HS256',
];

