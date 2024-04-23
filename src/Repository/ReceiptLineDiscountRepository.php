<?php

namespace App\Repository;

use App\Entity\ReceiptLineDiscount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReceiptLineDiscount>
 *
 * @method ReceiptLineDiscount|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReceiptLineDiscount|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReceiptLineDiscount[]    findAll()
 * @method ReceiptLineDiscount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReceiptLineDiscountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReceiptLineDiscount::class);
    }

//    /**
//     * @return ReceiptLineDiscount[] Returns an array of ReceiptLineDiscount objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ReceiptLineDiscount
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
