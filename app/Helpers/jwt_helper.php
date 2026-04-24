<?php

/**
 * JWT Helper Functions
 * 
 * Utility functions for encoding/decoding JWT tokens
 * using the firebase/php-jwt library.
 */

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!function_exists('jwt_encode')) {
    /**
     * Create a JWT token for a user
     *
     * @param array $userData User data to encode in token
     * @return string Encoded JWT token
     */
    function jwt_encode(array $userData): string
    {
        $key    = env('JWT_SECRET', 'default_secret_change_me');
        $issuer = env('JWT_ISSUER', 'attendance_system');
        $expiry = (int) env('JWT_EXPIRY', 86400);

        $payload = [
            'iss'  => $issuer,                    // Issuer
            'iat'  => time(),                      // Issued at
            'exp'  => time() + $expiry,            // Expiration
            'sub'  => $userData['id'] ?? 0,        // Subject (user ID)
            'data' => [
                'id'       => $userData['id'] ?? 0,
                'username' => $userData['username'] ?? '',
                'role'     => $userData['role'] ?? 'super_admin',
            ],
        ];

        return JWT::encode($payload, $key, 'HS256');
    }
}

if (!function_exists('jwt_decode_token')) {
    /**
     * Decode and validate a JWT token
     *
     * @param string $token JWT token to decode
     * @return object|null Decoded token payload, or null if invalid
     */
    function jwt_decode_token(string $token): ?object
    {
        $key = env('JWT_SECRET', 'default_secret_change_me');

        try {
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            return $decoded;
        } catch (\Firebase\JWT\ExpiredException $e) {
            log_message('warning', 'JWT token expired: ' . $e->getMessage());
            return null;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            log_message('warning', 'JWT signature invalid: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            log_message('error', 'JWT decode error: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('jwt_extract_token')) {
    /**
     * Extract JWT token from Authorization header
     *
     * @param string $authHeader Authorization header value
     * @return string|null Token string or null
     */
    function jwt_extract_token(string $authHeader): ?string
    {
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }
}
