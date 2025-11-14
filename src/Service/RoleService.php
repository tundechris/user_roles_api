<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Role;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RoleService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RoleRepository $roleRepository,
        private readonly ValidatorInterface $validator
    ) {}

    /**
     * Create a new role.
     *
     * @param array<string, mixed> $data
     * @throws \InvalidArgumentException
     */
    public function createRole(array $data): Role
    {
        $role = new Role();
        $role->setName($data['name'] ?? '');

        if (isset($data['description'])) {
            $role->setDescription($data['description']);
        }

        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $role->setPermissions($data['permissions']);
        }

        // Validate role
        $errors = $this->validator->validate($role);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . json_encode($errorMessages));
        }

        // Check if role name already exists
        $existingRole = $this->roleRepository->findByName($role->getName());
        if ($existingRole) {
            throw new \InvalidArgumentException('Role with name "' . $role->getName() . '" already exists');
        }

        $this->entityManager->persist($role);
        $this->entityManager->flush();

        return $role;
    }

    /**
     * Update an existing role.
     *
     * @param array<string, mixed> $data
     * @throws \InvalidArgumentException
     */
    public function updateRole(Role $role, array $data): Role
    {
        if (isset($data['name'])) {
            // Check if new name already exists (excluding current role)
            $existingRole = $this->roleRepository->findByName($data['name']);
            if ($existingRole && $existingRole->getId() !== $role->getId()) {
                throw new \InvalidArgumentException('Role with name "' . $data['name'] . '" already exists');
            }
            $role->setName($data['name']);
        }

        if (isset($data['description'])) {
            $role->setDescription($data['description']);
        }

        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $role->setPermissions($data['permissions']);
        }

        // Validate role
        $errors = $this->validator->validate($role);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . json_encode($errorMessages));
        }

        $this->entityManager->flush();

        return $role;
    }

    /**
     * Delete a role.
     */
    public function deleteRole(Role $role): void
    {
        $this->entityManager->remove($role);
        $this->entityManager->flush();
    }

    /**
     * Find role by ID.
     */
    public function findRoleById(int $id): ?Role
    {
        return $this->roleRepository->find($id);
    }

    /**
     * Find role by name.
     */
    public function findRoleByName(string $name): ?Role
    {
        return $this->roleRepository->findByName($name);
    }

    /**
     * Find all roles.
     *
     * @return Role[]
     */
    public function findAllRoles(): array
    {
        return $this->roleRepository->findAll();
    }

    /**
     * Get role with users.
     */
    public function getRoleWithUsers(int $id): ?Role
    {
        return $this->roleRepository->findOneWithUsers($id);
    }

    /**
     * Search roles by name pattern.
     *
     * @return Role[]
     */
    public function searchRoles(string $pattern): array
    {
        return $this->roleRepository->findByNamePattern($pattern);
    }

    /**
     * Get all role names.
     *
     * @return string[]
     */
    public function getAllRoleNames(): array
    {
        return $this->roleRepository->getAllRoleNames();
    }

    /**
     * Get role statistics.
     *
     * @return array<string, int>
     */
    public function getRoleStatistics(): array
    {
        $unusedRoles = $this->roleRepository->findUnusedRoles();

        return [
            'total' => $this->roleRepository->countAll(),
            'unused' => count($unusedRoles),
        ];
    }

    /**
     * Get roles with user counts.
     *
     * @return array<int, array{role: Role, userCount: int}>
     */
    public function getRolesWithUserCount(): array
    {
        return $this->roleRepository->findAllWithUserCount();
    }
}
