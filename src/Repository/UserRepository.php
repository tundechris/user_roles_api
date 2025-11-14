<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Find user by username or email.
     */
    public function findByUsernameOrEmail(string $identifier): ?User
    {
        return $this->createQueryBuilder('u')
            ->where('u.username = :identifier OR u.email = :identifier')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all active users with their roles.
     *
     * @return User[]
     */
    public function findAllActive(): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.roles', 'r')
            ->addSelect('r')
            ->where('u.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find users by role name.
     *
     * @return User[]
     */
    public function findByRoleName(string $roleName): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.roles', 'r')
            ->addSelect('r')
            ->where('r.name = :roleName')
            ->setParameter('roleName', $roleName)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find one user with roles (eager loading).
     */
    public function findOneWithRoles(int $id): ?User
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.roles', 'r')
            ->addSelect('r')
            ->where('u.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Search users by username or email.
     *
     * @return User[]
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.roles', 'r')
            ->addSelect('r')
            ->where('u.username LIKE :query OR u.email LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('u.username', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total users.
     */
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count active users.
     */
    public function countActive(): int
    {
        return (int) $this->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
