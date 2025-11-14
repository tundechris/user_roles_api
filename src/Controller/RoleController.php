<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\RoleService;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/roles', name: 'api_roles_')]
class RoleController extends AbstractController
{
    public function __construct(
        private readonly RoleService $roleService,
        private readonly SerializerInterface $serializer
    ) {}

    /**
     * List all roles with pagination.
     */
    #[OA\Get(
        path: '/api/roles',
        summary: 'List all roles',
        security: [['Bearer' => []]],
        tags: ['Roles'],
        parameters: [
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 1),
                description: 'Page number',
                example: 1
            ),
            new OA\Parameter(
                name: 'limit',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', default: 20, minimum: 1, maximum: 100),
                description: 'Items per page (max 100)',
                example: 20
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'name', type: 'string', example: 'ROLE_ADMIN'),
                                    new OA\Property(property: 'description', type: 'string', example: 'Administrator role')
                                ],
                                type: 'object'
                            )
                        ),
                        new OA\Property(
                            property: 'pagination',
                            properties: [
                                new OA\Property(property: 'page', type: 'integer', example: 1),
                                new OA\Property(property: 'limit', type: 'integer', example: 20),
                                new OA\Property(property: 'total', type: 'integer', example: 50),
                                new OA\Property(property: 'total_pages', type: 'integer', example: 3)
                            ],
                            type: 'object'
                        )
                    ]
                )
            )
        ]
    )]
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = (int) $request->query->get('page', 1);
            $limit = (int) $request->query->get('limit', 20);

            $result = $this->roleService->findRolesPaginated($page, $limit);

            $data = $this->serializer->normalize($result['roles'], null, [
                'groups' => ['role:read'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $this->json([
                'status' => 'success',
                'data' => $data,
                'pagination' => [
                    'page' => $result['page'],
                    'limit' => $result['limit'],
                    'total' => $result['total'],
                    'total_pages' => $result['total_pages']
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get role by ID.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            $role = $this->roleService->findRoleById($id);

            if (!$role) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Role not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = $this->serializer->normalize($role, null, [
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
     * Create new role.
     */
    #[OA\Post(
        path: '/api/roles',
        summary: 'Create new role',
        security: [['Bearer' => []]],
        tags: ['Roles'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'ROLE_ADMIN'),
                    new OA\Property(property: 'description', type: 'string', example: 'Administrator role')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Role created successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'message', type: 'string', example: 'Role created successfully'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'ROLE_ADMIN'),
                                new OA\Property(property: 'description', type: 'string', example: 'Administrator role')
                            ],
                            type: 'object'
                        )
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid input data')
        ]
    )]
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

            $role = $this->roleService->createRole($data);

            $responseData = $this->serializer->normalize($role, null, [
                'groups' => ['role:read'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $this->json([
                'status' => 'success',
                'message' => 'Role created successfully',
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
     * Update role.
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $role = $this->roleService->findRoleById($id);

            if (!$role) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Role not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Invalid JSON'
                ], Response::HTTP_BAD_REQUEST);
            }

            $role = $this->roleService->updateRole($role, $data);

            $responseData = $this->serializer->normalize($role, null, [
                'groups' => ['role:read'],
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);

            return $this->json([
                'status' => 'success',
                'message' => 'Role updated successfully',
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
     * Partial update role.
     */
    #[Route('/{id}', name: 'patch', methods: ['PATCH'], requirements: ['id' => '\d+'])]
    public function patch(int $id, Request $request): JsonResponse
    {
        return $this->update($id, $request);
    }

    /**
     * Delete role.
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        try {
            $role = $this->roleService->findRoleById($id);

            if (!$role) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Role not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $this->roleService->deleteRole($role);

            return $this->json([
                'status' => 'success',
                'message' => 'Role deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get users with this role.
     */
    #[Route('/{id}/users', name: 'users', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function getUsers(int $id): JsonResponse
    {
        try {
            $role = $this->roleService->getRoleWithUsers($id);

            if (!$role) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Role not found'
                ], Response::HTTP_NOT_FOUND);
            }

            $users = $role->getUsers()->toArray();

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
     * Search roles.
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

            $roles = $this->roleService->searchRoles($query);

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
     * Get role statistics.
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->roleService->getRoleStatistics();

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
