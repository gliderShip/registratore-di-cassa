<?php

/* (c) Erin Hima <erinhima@gmail.com> */

namespace App\Repository;

use App\Entity\ReceiptLine;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ReceiptLine>
 *
 * @method ReceiptLine|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReceiptLine|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReceiptLine[]    findAll()
 * @method ReceiptLine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReceiptLineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReceiptLine::class);
    }
}
