<?php

/* (c) Erin Hima <erihima@gmail.com> */

namespace App\Repository;

use App\Entity\ProductVatCategory;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ProductVatCategory>
 *
 * @method ProductVatCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProductVatCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProductVatCategory[]    findAll()
 * @method ProductVatCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductVatCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProductVatCategory::class);
    }

//    /**
//     * @return ProductClass[] Returns an array of ProductClass objects
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

//    public function findOneBySomeField($value): ?ProductClass
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
