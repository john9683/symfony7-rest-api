<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

  /**
   * @param bool $value
   * @return User[]
   */
    public function getAllVerifiedUser(bool $value): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isVerified = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
