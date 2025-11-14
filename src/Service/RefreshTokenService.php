<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\RefreshToken;
use App\Entity\User;
use App\Repository\RefreshTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

class RefreshTokenService
{
    private const REFRESH_TOKEN_TTL = 2592000; // 30 days in seconds

    public function __construct(
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function createRefreshToken(User $user): RefreshToken
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setUser($user);
        $refreshToken->setToken($this->generateUniqueToken());

        $expiresAt = new \DateTime();
        $expiresAt->modify(sprintf('+%d seconds', self::REFRESH_TOKEN_TTL));
        $refreshToken->setExpiresAt($expiresAt);

        $this->entityManager->persist($refreshToken);
        $this->entityManager->flush();

        return $refreshToken;
    }

    public function validateRefreshToken(string $tokenString): ?RefreshToken
    {
        return $this->refreshTokenRepository->findValidTokenByTokenString($tokenString);
    }

    public function revokeRefreshToken(RefreshToken $refreshToken): void
    {
        $refreshToken->setIsRevoked(true);
        $this->entityManager->flush();
    }

    public function revokeAllUserTokens(User $user): void
    {
        $this->refreshTokenRepository->revokeAllUserTokens($user);
    }

    public function cleanupExpiredTokens(): int
    {
        return $this->refreshTokenRepository->deleteExpiredTokens();
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
            $existing = $this->refreshTokenRepository->findOneBy(['token' => $token]);
        } while ($existing !== null);

        return $token;
    }
}
