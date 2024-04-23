<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Interface\DomainEntityManagerInterface;

readonly class ProductManager implements DomainEntityManagerInterface
{

    public function __construct(
        private ProductRepository $repository,
        private EntityManagerInterface $em
    ) {
    }

    public function getRepository(): ProductRepository
    {
        return $this->repository;
    }

    public function getEm(): EntityManagerInterface
    {
        return $this->em;
    }

    /** @return array<int, array<string, mixed>> */
    public function getProductTable(): array
    {
        $productNames = $this->repository
            ->createQueryBuilder('p')
            ->select('p.barCode', 'p.name', 'p.listPriceAmount', 'p.listPriceType')
            ->getQuery()
            ->getResult();

        return $productNames;
    }

    public function getByName(string $name): ?Product
    {
        return $this->repository->findOneBy(['name' => $name]);
    }


}
