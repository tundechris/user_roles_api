<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly RoleRepository $roleRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator
    ) {}

    /**
     * Create a new user.
     *
     * @param array<string, mixed> $data
     * @throws \InvalidArgumentException
     */
    public function createUser(array $data): User
    {
        $user = new User();
        $user->setUsername($data['username'] ?? '');
        $user->setEmail($data['email'] ?? '');

        // Hash password
        if (empty($data['password'])) {
            throw new \InvalidArgumentException('Password is required');
        }
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        // Set active status
        if (isset($data['isActive'])) {
            $user->setIsActive((bool) $data['isActive']);
        }

        // Validate user
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . json_encode($errorMessages));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Update an existing user.
     *
     * @param array<string, mixed> $data
     * @throws \InvalidArgumentException
     */
    public function updateUser(User $user, array $data): User
    {
        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        if (isset($data['isActive'])) {
            $user->setIsActive((bool) $data['isActive']);
        }

        // Validate user
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            throw new \InvalidArgumentException('Validation failed: ' . json_encode($errorMessages));
        }

        $this->entityManager->flush();

        return $user;
    }

    /**
     * Delete a user.
     */
    public function deleteUser(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    /**
     * Find user by ID.
     */
    public function findUserById(int $id): ?User
    {
        return $this->userRepository->findOneWithRoles($id);
    }

    /**
     * Find all users.
     *
     * @return User[]
     */
    public function findAllUsers(): array
    {
        return $this->userRepository->findAllActive();
    }

    /**
     * Find users with pagination.
     *
     * @return array{users: User[], total: int, page: int, limit: int, total_pages: int}
     */
    public function findUsersPaginated(int $page = 1, int $limit = 20): array
    {
        // Ensure page is at least 1
        $page = max(1, $page);

        // Ensure limit is between 1 and 100
        $limit = max(1, min(100, $limit));

        return $this->userRepository->findPaginated($page, $limit);
    }

    /**
     * Assign roles to user.
     *
     * @param int[] $roleIds
     * @throws \InvalidArgumentException
     */
    public function assignRoles(User $user, array $roleIds): User
    {
        // Clear existing roles
        foreach ($user->getRoleEntities() as $role) {
            $user->removeRole($role);
        }

        // Add new roles
        foreach ($roleIds as $roleId) {
            $role = $this->roleRepository->find($roleId);
            if (!$role) {
                throw new \InvalidArgumentException("Role with ID {$roleId} not found");
            }
            $user->addRole($role);
        }

        $this->entityManager->flush();

        return $user;
    }

    /**
     * Add single role to user.
     *
     * @throws \InvalidArgumentException
     */
    public function addRole(User $user, int $roleId): User
    {
        $role = $this->roleRepository->find($roleId);
        if (!$role) {
            throw new \InvalidArgumentException("Role with ID {$roleId} not found");
        }

        $user->addRole($role);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Remove single role from user.
     *
     * @throws \InvalidArgumentException
     */
    public function removeRole(User $user, int $roleId): User
    {
        $role = $this->roleRepository->find($roleId);
        if (!$role) {
            throw new \InvalidArgumentException("Role with ID {$roleId} not found");
        }

        $user->removeRole($role);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * Search users.
     *
     * @return User[]
     */
    public function searchUsers(string $query): array
    {
        return $this->userRepository->search($query);
    }

    /**
     * Get user statistics.
     *
     * @return array<string, int>
     */
    public function getUserStatistics(): array
    {
        return [
            'total' => $this->userRepository->countAll(),
            'active' => $this->userRepository->countActive(),
        ];
    }
}
