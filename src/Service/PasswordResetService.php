<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PasswordResetRequest;
use App\Entity\User;
use App\Repository\PasswordResetRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordResetService
{
    private const RESET_TOKEN_TTL = 3600; // 1 hour in seconds

    public function __construct(
        private readonly PasswordResetRequestRepository $resetRequestRepository,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {}

    /**
     * Create a password reset request for a user.
     */
    public function createResetRequest(string $email): ?PasswordResetRequest
    {
        $user = $this->userRepository->findByUsernameOrEmail($email);

        if (!$user || !$user->getIsActive()) {
            // Return null for security reasons (don't reveal if email exists)
            return null;
        }

        // Invalidate all existing reset tokens for this user
        $this->resetRequestRepository->invalidateAllUserTokens($user);

        $resetRequest = new PasswordResetRequest();
        $resetRequest->setUser($user);
        $resetRequest->setToken($this->generateUniqueToken());

        $expiresAt = new \DateTime();
        $expiresAt->modify(sprintf('+%d seconds', self::RESET_TOKEN_TTL));
        $resetRequest->setExpiresAt($expiresAt);

        $this->entityManager->persist($resetRequest);
        $this->entityManager->flush();

        return $resetRequest;
    }

    /**
     * Validate a reset token.
     */
    public function validateResetToken(string $token): ?PasswordResetRequest
    {
        return $this->resetRequestRepository->findValidTokenByTokenString($token);
    }

    /**
     * Reset password using a valid token.
     */
    public function resetPassword(string $token, string $newPassword): bool
    {
        $resetRequest = $this->validateResetToken($token);

        if (!$resetRequest || !$resetRequest->isValid()) {
            return false;
        }

        $user = $resetRequest->getUser();

        // Hash and set new password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);

        // Mark reset request as used
        $resetRequest->setIsUsed(true);

        $this->entityManager->flush();

        return true;
    }

    /**
     * Clean up expired reset tokens.
     */
    public function cleanupExpiredTokens(): int
    {
        return $this->resetRequestRepository->deleteExpiredTokens();
    }

    private function generateUniqueToken(): string
    {
        do {
            $token = bin2hex(random_bytes(32));
            $existing = $this->resetRequestRepository->findOneBy(['token' => $token]);
        } while ($existing !== null);

        return $token;
    }
}
