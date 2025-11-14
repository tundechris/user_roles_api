<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/users', name: 'api_users_')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly UserService $userService,
        private readonly SerializerInterface $serializer
    ) {}

    /**
     * List all users.
     */
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        try {
            $users = $this->userService->findAllUsers();

            $data = $this->serializer->normalize($users, null, [
                'groups' => ['user:read'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $this->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user by ID.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            $user = $this->userService->findUserById($id);

            if (!$user) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = $this->serializer->normalize($user, null, [
                'groups' => ['user:read'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $this->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create new user.
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Invalid JSON'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->userService->createUser($data);

            $responseData = $this->serializer->normalize($user, null, [
                'groups' => ['user:read'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $this->json([
                'status' => 'success',
                'message' => 'User created successfully',
                'data' => $responseData
            ], Response::HTTP_CREATED);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update user.
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->userService->findUserById($id);

            if (!$user) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Invalid JSON'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->userService->updateUser($user, $data);

            $responseData = $this->serializer->normalize($user, null, [
                'groups' => ['user:read'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $this->json([
                'status' => 'success',
                'message' => 'User updated successfully',
                'data' => $responseData
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Partial update user.
     */
    #[Route('/{id}', name: 'patch', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function patch(int $id, Request $request): JsonResponse
    {
        return $this->update($id, $request);
    }

    /**
     * Delete user.
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $user = $this->userService->findUserById($id);

            if (!$user) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $this->userService->deleteUser($user);

            return $this->json([
                'status' => 'success',
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user's roles.
     */
    #[Route('/{id}/roles', name: 'roles', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getRoles(int $id): JsonResponse
    {
        try {
            $user = $this->userService->findUserById($id);

            if (!$user) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $roles = $user->getRoleEntities()->toArray();

            $data = $this->serializer->normalize($roles, null, [
                'groups' => ['role:read'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $this->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Assign roles to user.
     */
    #[Route('/{id}/roles', name: 'assign_roles', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function assignRoles(int $id, Request $request): JsonResponse
    {
        try {
            $user = $this->userService->findUserById($id);

            if (!$user) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (!isset($data['roleIds']) || !is_array($data['roleIds'])) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Invalid request: roleIds array required'
                ], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->userService->assignRoles($user, $data['roleIds']);

            $responseData = $this->serializer->normalize($user, null, [
                'groups' => ['user:read'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $this->json([
                'status' => 'success',
                'message' => 'Roles assigned successfully',
                'data' => $responseData
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove role from user.
     */
    #[Route('/{id}/roles/{roleId}', name: 'remove_role', methods: ['DELETE'], requirements: ['id' => '\d+', 'roleId' => '\d+'])]
    public function removeRole(int $id, int $roleId): JsonResponse
    {
        try {
            $user = $this->userService->findUserById($id);

            if (!$user) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $user = $this->userService->removeRole($user, $roleId);

            return $this->json([
                'status' => 'success',
                'message' => 'Role removed successfully'
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search users.
     */
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->query->get('q', '');

            if (empty($query)) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Search query parameter "q" is required'
                ], Response::HTTP_BAD_REQUEST);
            }

            $users = $this->userService->searchUsers($query);

            $data = $this->serializer->normalize($users, null, [
                'groups' => ['user:read'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $this->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get user statistics.
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->userService->getUserStatistics();

            return $this->json([
                'status' => 'success',
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
