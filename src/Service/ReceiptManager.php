<?php

namespace App\Service;

use App\Entity\Receipt;
use App\Entity\Product;
use App\Entity\ReceiptLine;
use App\Repository\ReceiptRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Interface\DomainEntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

readonly class ReceiptManager implements DomainEntityManagerInterface
{
    public function __construct(
        private ReceiptRepository $repository,
        private EntityManagerInterface $em,
        private DiscountManager $discountManager,
        private ValidatorInterface $validator,
    ) {
    }

    public function getRepository(): ReceiptRepository
    {
        return $this->repository;
    }

    public function getEm(): EntityManagerInterface
    {
        return $this->em;
    }

    public function addProductToReceipt(Receipt &$receipt, Product $product, int|float $quantity): void
    {
        if ($receipt->isClosed()) {
            throw new \Exception('Cannot add product to a closed receipt!');
        }

        $receiptLine = $receipt->getReceiptLine($product);
        if ($receiptLine) {
            $receiptLine->setQuantity($receiptLine->addQuantity($quantity));
        } else {
            $receiptLine = $this->createReceiptLine($product, $quantity);
        }

        $receipt->addReceiptLine($receiptLine);

        $this->discountManager->updateProductDiscounts($receipt, $product);

        $errors = $this->validator->validate($receipt);
        if (count($errors) > 0) {
            throw new ValidationFailedException($receipt, $errors);
        }

        $this->save($receipt);
    }

    private function createReceiptLine(Product $product, int|float $quantity): ReceiptLine
    {
        $receiptLine = new ReceiptLine($product, $quantity);

        return $receiptLine;
    }

    public function save(Receipt $receipt): void
    {
        $errors = $this->validator->validate($receipt);
        if (count($errors) > 0) {
            throw new ValidationFailedException($receipt, $errors);
        }

        $this->em->persist($receipt);
        $this->em->flush();
    }


}
