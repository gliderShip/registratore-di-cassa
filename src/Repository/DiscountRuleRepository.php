<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Erin Hima <erihima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Repository;

use App\Entity\DiscountRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DiscountRule>
 *
 * @method DiscountRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method DiscountRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method DiscountRule[]    findAll()
 * @method DiscountRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscountRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DiscountRule::class);
    }

    //    /**
    //     * @return DiscountRule[] Returns an array of DiscountRule objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('d.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?DiscountRule
    //    {
    //        return $this->createQueryBuilder('d')
    //            ->andWhere('d.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
