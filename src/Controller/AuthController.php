<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    /**
     * User login endpoint.
     *
     * This endpoint is handled by Lexik JWT Authentication Bundle.
     * Configuration is in security.yaml under the 'login' firewall.
     *
     * Expected request body:
     * {
     *     "username": "string",
     *     "password": "string"
     * }
     *
     * Success response (handled by JWT bundle):
     * {
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
     * }
     */
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
     * Token refresh endpoint placeholder.
     *
     * This can be implemented using refresh tokens if needed.
     * For now, users need to login again when token expires.
     */
    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(): JsonResponse
    {
        return $this->json([
            'status' => 'error',
            'message' => 'Token refresh not yet implemented. Please login again.'
        ], Response::HTTP_NOT_IMPLEMENTED);
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
