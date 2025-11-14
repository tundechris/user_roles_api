<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\PasswordResetService;
use App\Service\RefreshTokenService;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly RefreshTokenService $refreshTokenService,
        private readonly JWTTokenManagerInterface $jwtManager,
        private readonly PasswordResetService $passwordResetService
    ) {}

    /**
     * User login endpoint.
     *
     * This endpoint is handled by Lexik JWT Authentication Bundle with custom success handler.
     * Configuration is in security.yaml under the 'login' firewall.
     *
     * Expected request body:
     * {
     *     "username": "string",
     *     "password": "string"
     * }
     *
     * Success response (handled by custom success handler):
     * {
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
     *     "refresh_token": "a1b2c3d4e5f6...",
     *     "expires_in": 3600,
     *     "refresh_expires_in": 2592000
     * }
     */
    #[OA\Post(
        path: '/api/auth/login',
        summary: 'User login',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful login',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...'),
                        new OA\Property(property: 'refresh_token', type: 'string', example: 'a1b2c3d4e5f6...'),
                        new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                        new OA\Property(property: 'refresh_expires_in', type: 'integer', example: 2592000)
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Invalid credentials')
        ]
    )]
    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): never
    {
        // This method will never be executed - it's handled by security.yaml
        // But we need it for the route definition
        throw new \LogicException('This method should not be called directly.');
    }

    /**
     * User registration endpoint.
     *
     * Expected request body:
     * {
     *     "username": "string",
     *     "email": "string",
     *     "password": "string"
     * }
     */
    #[OA\Post(
        path: '/api/auth/register',
        summary: 'User registration',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['username', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'User registered successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'User registered successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'username', type: 'string', example: 'johndoe'),
                                new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                                new OA\Property(property: 'isActive', type: 'boolean', example: true),
                                new OA\Property(property: 'createdAt', type: 'string', format: 'date-time')
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input data')
        ]
    )]
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Invalid JSON'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validate required fields
            $requiredFields = ['username', 'email', 'password'];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Missing required fields',
                    'missing_fields' => $missingFields
                ], Response::HTTP_BAD_REQUEST);
            }

            // Create user
            $user = $this->userService->createUser($data);

            return $this->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                    'isActive' => $user->getIsActive(),
                    'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s')
                ]
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'An error occurred during registration'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Token refresh endpoint.
     *
     * Use this endpoint to refresh an expired JWT access token using a valid refresh token.
     * The refresh token is obtained during login and is valid for 30 days.
     *
     * Expected request body:
     * {
     *     "refresh_token": "string"
     * }
     */
    #[OA\Post(
        path: '/api/auth/refresh',
        summary: 'Refresh JWT access token',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['refresh_token'],
                properties: [
                    new OA\Property(property: 'refresh_token', type: 'string', example: 'a1b2c3d4e5f6...')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Token refreshed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'token', type: 'string', example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...'),
                        new OA\Property(property: 'refresh_token', type: 'string', example: 'a1b2c3d4e5f6...'),
                        new OA\Property(property: 'expires_in', type: 'integer', example: 3600),
                        new OA\Property(property: 'refresh_expires_in', type: 'integer', example: 2592000)
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid or missing refresh token'),
            new OA\Response(response: 401, description: 'Refresh token expired or revoked')
        ]
    )]
    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['refresh_token']) || empty($data['refresh_token'])) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Refresh token is required'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validate refresh token
            $refreshToken = $this->refreshTokenService->validateRefreshToken($data['refresh_token']);

            if (!$refreshToken || !$refreshToken->isValid()) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired refresh token'
                ], Response::HTTP_UNAUTHORIZED);
            }

            $user = $refreshToken->getUser();

            if (!$user->getIsActive()) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'User account is inactive'
                ], Response::HTTP_UNAUTHORIZED);
            }

            // Generate new JWT access token
            $jwt = $this->jwtManager->create($user);

            // Revoke old refresh token
            $this->refreshTokenService->revokeRefreshToken($refreshToken);

            // Generate new refresh token
            $newRefreshToken = $this->refreshTokenService->createRefreshToken($user);

            return $this->json([
                'token' => $jwt,
                'refresh_token' => $newRefreshToken->getToken(),
                'expires_in' => (int) ($_ENV['JWT_TOKEN_TTL'] ?? 3600),
                'refresh_expires_in' => 2592000 // 30 days
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'An error occurred during token refresh'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Request password reset endpoint.
     *
     * Sends a password reset token to the user's email.
     * For security, always returns success even if email doesn't exist.
     */
    #[OA\Post(
        path: '/api/auth/password-reset/request',
        summary: 'Request password reset',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password reset email sent (if email exists)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'If the email exists, a password reset link has been sent')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input data')
        ]
    )]
    #[Route('/password-reset/request', name: 'password_reset_request', methods: ['POST'])]
    public function requestPasswordReset(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['email']) || empty($data['email'])) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Email is required'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Create reset request (returns null if email doesn't exist for security)
            $resetRequest = $this->passwordResetService->createResetRequest($data['email']);

            // Always return success for security (don't reveal if email exists)
            // In production, you would send an email with the reset token here
            // For now, we'll include the token in the response for testing
            $responseData = [
                'status' => 'success',
                'message' => 'If the email exists, a password reset link has been sent'
            ];

            // In development/testing, include the token (remove in production!)
            if ($resetRequest && $_ENV['APP_ENV'] === 'dev') {
                $responseData['reset_token'] = $resetRequest->getToken();
            }

            return $this->json($responseData);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'An error occurred processing your request'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reset password endpoint.
     *
     * Resets the user's password using a valid reset token.
     */
    #[OA\Post(
        path: '/api/auth/password-reset/confirm',
        summary: 'Reset password with token',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['token', 'password'],
                properties: [
                    new OA\Property(property: 'token', type: 'string', example: 'a1b2c3d4e5f6...'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'newSecurePassword123')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password reset successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Password has been reset successfully')
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid or expired token'),
            new OA\Response(response: 401, description: 'Invalid token')
        ]
    )]
    #[Route('/password-reset/confirm', name: 'password_reset_confirm', methods: ['POST'])]
    public function confirmPasswordReset(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['token']) || empty($data['token'])) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Reset token is required'
                ], Response::HTTP_BAD_REQUEST);
            }

            if (!isset($data['password']) || empty($data['password'])) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'New password is required'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Validate password length
            if (strlen($data['password']) < 8) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Password must be at least 8 characters long'
                ], Response::HTTP_BAD_REQUEST);
            }

            $success = $this->passwordResetService->resetPassword($data['token'], $data['password']);

            if (!$success) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired reset token'
                ], Response::HTTP_UNAUTHORIZED);
            }

            return $this->json([
                'status' => 'success',
                'message' => 'Password has been reset successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => 'An error occurred during password reset'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Logout endpoint placeholder.
     *
     * Since JWT tokens are stateless, logout is typically handled client-side
     * by removing the token from storage.
     */
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'message' => 'Please remove the token from your client storage'
        ]);
    }
}
