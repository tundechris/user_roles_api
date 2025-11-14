<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User;
use App\Service\RefreshTokenService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class JWTAuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly RefreshTokenService $refreshTokenService
    ) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): JsonResponse
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'Invalid user'
            ], JsonResponse::HTTP_UNAUTHORIZED);
        }

        // Generate JWT access token
        $jwt = $this->jwtManager->create($user);

        // Generate refresh token
        $refreshToken = $this->refreshTokenService->createRefreshToken($user);

        return new JsonResponse([
            'token' => $jwt,
            'refresh_token' => $refreshToken->getToken(),
            'expires_in' => (int) ($_ENV['JWT_TOKEN_TTL'] ?? 3600),
            'refresh_expires_in' => 2592000 // 30 days
        ]);
    }
}
