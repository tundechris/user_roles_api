<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PasswordResetRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordResetRequest>
 */
class PasswordResetRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetRequest::class);
    }

    public function save(PasswordResetRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PasswordResetRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findValidTokenByTokenString(string $tokenString): ?PasswordResetRequest
    {
        return $this->createQueryBuilder('prr')
            ->where('prr.token = :token')
            ->andWhere('prr.isUsed = false')
            ->andWhere('prr.expiresAt > :now')
            ->setParameter('token', $tokenString)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function invalidateAllUserTokens(User $user): void
    {
        $this->createQueryBuilder('prr')
            ->update()
            ->set('prr.isUsed', 'true')
            ->where('prr.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    public function deleteExpiredTokens(): int
    {
        return $this->createQueryBuilder('prr')
            ->delete()
            ->where('prr.expiresAt < :now')
            ->orWhere('prr.isUsed = true')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->execute();
    }
}
