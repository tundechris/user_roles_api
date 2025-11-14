<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Role>
 */
class RoleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * Find role by name.
     */
    public function findByName(string $name): ?Role
    {
        return $this->createQueryBuilder('r')
            ->where('r.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all roles with user count.
     *
     * @return array<int, array{role: Role, userCount: int}>
     */
    public function findAllWithUserCount(): array
    {
        $roles = $this->createQueryBuilder('r')
            ->leftJoin('r.users', 'u')
            ->addSelect('COUNT(u.id) as userCount')
            ->groupBy('r.id')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $roles;
    }

    /**
     * Find one role with users (eager loading).
     */
    public function findOneWithUsers(int $id): ?Role
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.users', 'u')
            ->addSelect('u')
            ->where('r.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find roles by name pattern.
     *
     * @return Role[]
     */
    public function findByNamePattern(string $pattern): array
    {
        return $this->createQueryBuilder('r')
            ->where('r.name LIKE :pattern')
            ->setParameter('pattern', '%' . $pattern . '%')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Get all role names.
     *
     * @return string[]
     */
    public function getAllRoleNames(): array
    {
        $result = $this->createQueryBuilder('r')
            ->select('r.name')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'name');
    }

    /**
     * Count total roles.
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find roles with no users assigned.
     *
     * @return Role[]
     */
    public function findUnusedRoles(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.users', 'u')
            ->having('COUNT(u.id) = 0')
            ->groupBy('r.id')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find roles with pagination.
     *
     * @return array{roles: Role[], total: int, page: int, limit: int, total_pages: int}
     */
    public function findPaginated(int $page = 1, int $limit = 20): array
    {
        $query = $this->createQueryBuilder('r')
            ->leftJoin('r.users', 'u')
            ->addSelect('u')
            ->orderBy('r.name', 'ASC');

        // Count total before pagination
        $total = (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Apply pagination
        $offset = ($page - 1) * $limit;
        $roles = $query
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'roles' => $roles,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => (int) ceil($total / $limit)
        ];
    }
}
