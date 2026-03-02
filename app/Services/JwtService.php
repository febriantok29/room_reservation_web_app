<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;
use Exception;

class JwtService
{
    private string $secretKey;
    private string $algorithm = 'HS256';
    private int $accessTokenTtl = 900; // 15 minutes in seconds
    private int $refreshTokenTtl = 604800; // 7 days in seconds

    public function __construct()
    {
        $this->secretKey = config('app.jwt_secret') ?? env('JWT_SECRET', 'change-me-in-production');
    }

    /**
     * Issue both access and refresh tokens
     *
     * @param User $user
     * @param bool $isDebug Whether to override TTL for debugging
     * @param int|null $accessTokenTtlOverride Access token TTL in seconds (only if isDebug=true)
     * @param int|null $refreshTokenTtlOverride Refresh token TTL in seconds (only if isDebug=true)
     * @return array { access_token, refresh_token, token_type, expires_in }
     */
    public function issueTokens(
        User $user,
        bool $isDebug = false,
        ?int $accessTokenTtlOverride = null,
        ?int $refreshTokenTtlOverride = null
    ): array {
        $accessTtl = $isDebug && $accessTokenTtlOverride ? $accessTokenTtlOverride : $this->accessTokenTtl;
        $refreshTtl = $isDebug && $refreshTokenTtlOverride ? $refreshTokenTtlOverride : $this->refreshTokenTtl;

        $accessToken = $this->createToken($user, $accessTtl, 'access');
        $refreshToken = $this->createToken($user, $refreshTtl, 'refresh');

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $accessTtl,
            'is_debug' => $isDebug,
        ];
    }

    /**
     * Create a JWT token
     *
     * @param User $user
     * @param int $ttl Time to live in seconds
     * @param string $type Token type: 'access' or 'refresh'
     * @return string
     */
    private function createToken(User $user, int $ttl, string $type = 'access'): string
    {
        $issuedAt = now()->timestamp;
        $expiresAt = $issuedAt + $ttl;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'type' => $type,
            'sub' => $user->id,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->full_name,
                'employee_id' => $user->employee_id,
                'is_admin' => $user->is_admin,
            ],
        ];

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    /**
     * Verify and decode JWT token
     *
     * @param string $token
     * @return array|null { success: bool, data: stdClass|null, error: string|null }
     */
    public function verifyToken(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));

            return [
                'success' => true,
                'data' => $decoded,
                'error' => null,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Refresh access token using refresh token
     *
     * @param string $refreshToken
     * @param bool $isDebug Whether debug mode is active
     * @param int|null $accessTokenTtlOverride Access token TTL in seconds (only if isDebug=true)
     * @return array|null
     */
    public function refreshAccessToken(
        string $refreshToken,
        bool $isDebug = false,
        ?int $accessTokenTtlOverride = null
    ): ?array {
        $verification = $this->verifyToken($refreshToken);

        if (!$verification['success']) {
            return null;
        }

        $decoded = $verification['data'];

        // Check if token is a refresh token
        if ($decoded->type !== 'refresh') {
            return null;
        }

        // Get user from database
        $user = User::find($decoded->sub);

        if (!$user) {
            return null;
        }

        // Issue new access token
        $accessTtl = $isDebug && $accessTokenTtlOverride ? $accessTokenTtlOverride : $this->accessTokenTtl;
        $accessToken = $this->createToken($user, $accessTtl, 'access');

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => $accessTtl,
            'is_debug' => $isDebug,
        ];
    }

    /**
     * Get access token TTL (seconds)
     */
    public function getAccessTokenTtl(): int
    {
        return $this->accessTokenTtl;
    }

    /**
     * Get refresh token TTL (seconds)
     */
    public function getRefreshTokenTtl(): int
    {
        return $this->refreshTokenTtl;
    }
}
