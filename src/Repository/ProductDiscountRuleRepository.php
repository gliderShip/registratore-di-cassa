<?php

namespace App\Repository;

use App\Entity\ProductDiscountRule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProductDiscountRule>
 *
 * @method ProductDiscountRule|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductDiscountRule|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductDiscountRule[]    findAll()
 * @method ProductDiscountRule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductDiscountRuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductDiscountRule::class);
    }

//    /**
//     * @return ProductDiscountRule[] Returns an array of ProductDiscountRule objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ProductDiscountRule
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
