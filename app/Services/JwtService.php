<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtService
{
    /**
     * Get the JWT secret key.
     */
    protected function getSecretKey(): string
    {
        $key = config('jwt.secret') ?? env('JWT_SECRET') ?? config('app.key') ?? 'TaskManagementSecretKey2026';
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7));
            if ($decoded !== false) {
                return $decoded;
            }
        }
        return $key;
    }

    /**
     * Generate a new JWT token for a given user.
     *
     * @param User $user
     * @param int $ttlSeconds (Default: 7 days)
     * @return string
     */
    public function generateToken(User $user, int $ttlSeconds = 604800): string
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + $ttlSeconds;

        $payload = [
            'iss' => config('app.url', 'http://localhost:8000'),
            'sub' => $user->id,
            'iat' => $issuedAt,
            'exp' => $expirationTime,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ];

        return JWT::encode($payload, $this->getSecretKey(), 'HS256');
    }

    /**
     * Decode and validate a JWT token.
     *
     * @param string $token
     * @return object|null
     */
    public function validateToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->getSecretKey(), 'HS256'));
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Retrieve the User model from a JWT token.
     *
     * @param string $token
     * @return User|null
     */
    public function getUserFromToken(string $token): ?User
    {
        $payload = $this->validateToken($token);

        if (!$payload || !isset($payload->sub)) {
            return null;
        }

        return User::find($payload->sub);
    }
}
