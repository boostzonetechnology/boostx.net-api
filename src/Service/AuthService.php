<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Admin;
use App\Entity\AdminToken;
use App\Repository\AdminRepository;
use App\Repository\AdminTokenRepository;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;

final class AuthService
{
    public function __construct(
        private readonly AdminRepository $admins,
        private readonly AdminTokenRepository $tokens,
        private readonly int $tokenTtlSeconds,
    ) {
    }

    /** @return array{token: string, admin: array<string, mixed>} */
    public function login(string $email, string $password): array
    {
        $email = strtolower(trim($email));
        if ($email === '' || $password === '') {
            throw new InvalidArgumentException('Email and password are required.');
        }

        $admin = $this->admins->findByEmail($email);
        if (!$admin || !$admin->verifyPassword($password)) {
            throw new InvalidArgumentException('Invalid credentials.');
        }

        $this->tokens->purgeExpired();

        $tokenValue = bin2hex(random_bytes(32));
        $expires = (new DateTimeImmutable())->modify('+' . $this->tokenTtlSeconds . ' seconds');
        $token = new AdminToken($tokenValue, $admin, $expires);
        $this->tokens->save($token);

        return [
            'token' => $tokenValue,
            'admin' => $admin->toArray(),
        ];
    }

    public function logout(string $token): void
    {
        $row = $this->tokens->find($token);
        if ($row instanceof AdminToken) {
            $this->tokens->remove($row);
        }
    }

    public function authenticate(string $token): Admin
    {
        if ($token === '') {
            throw new RuntimeException('Login required.', 401);
        }

        $row = $this->tokens->findValid($token);
        if (!$row) {
            throw new RuntimeException('Session expired. Please login again.', 401);
        }

        return $row->getAdmin();
    }

    public function resolveBearer(?string $authorizationHeader, ?string $xAdminToken): string
    {
        if ($authorizationHeader && preg_match('/Bearer\s+(\S+)/i', $authorizationHeader, $m)) {
            return $m[1];
        }

        return trim((string) $xAdminToken);
    }
}
