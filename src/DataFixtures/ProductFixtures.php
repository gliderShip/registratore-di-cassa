<?php

/* (c) Erin Hima <erinhima@gmail.com> */

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use http\Exception\InvalidArgumentException;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public const AQUA_BARCODE = '0000000000000';
    public const PANE_BARCODE = '0000000000001';
    public const PERE_BARCODE = '0000000000002';
    public const BROCCOLI_BARCODE = '0000000000003';
    public const BANANE_BARCODE = '0000000000004';
    public const CAFFE_BARCODE = '0000000000005';
    public const LATTE_BARCODE = '0000000000006';
    public const UOVA_BARCODE = '0000000000007';
    public const POMODORI_BARCODE = '0000000000008';
    public const PROSCIUTTO_BARCODE = '0000000000009';
    public const SOTTILETTE_BARCODE = '0000000000010';
    public const POLENTA_BARCODE = '0000000000011';

    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    /** @return string[] */
    public function getDependencies(): array
    {
        return [VatCategoryFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getProductData() as [$barCode, $name, $vatCode, $listPriceAmount, $listPriceType]) {
            $vatCategory = $this->getReference($vatCode);
            if (!$vatCategory) {
                throw new InvalidArgumentException(sprintf('ProductVatCategory with code "%s" not found', $vatCode));
            }

            $product = new Product(
                barCode: $barCode,
                name: $name,
                vatCategory: $vatCategory,
                listPriceAmount: $listPriceAmount,
                listPriceType: $listPriceType
            );

            $errors = $this->validator->validate($product);
            if (count($errors) > 0) {
                throw new ValidationFailedException($product, $errors);
            }

            $manager->persist($product);
            $this->addReference($barCode, $product);
        }

        $manager->flush();
    }

    /** @return array<array{string, string, string, int, string}> */
    private function getProductData(): array
    {
        return [
            // $product = [$barCode, $name, $vatCode, $listPriceAmount, $listPriceType];
            [self::AQUA_BARCODE, 'Aqua Minerale', VatCategoryFixtures::VAT_CATEGORY_ZERO, 100, Product::PRICE_TYPE_UNIT],
            [self::PANE_BARCODE, 'Pane', VatCategoryFixtures::VAT_CATEGORY_REDUCED, 400, Product::PRICE_TYPE_WEIGHT],
            [self::PERE_BARCODE, 'Pere', VatCategoryFixtures::VAT_CATEGORY_STANDARD, 300, Product::PRICE_TYPE_WEIGHT],
            [self::BROCCOLI_BARCODE, 'Broccoli', VatCategoryFixtures::VAT_CATEGORY_STANDARD, 200, Product::PRICE_TYPE_UNIT],
            [self::BANANE_BARCODE, 'Banane', VatCategoryFixtures::VAT_CATEGORY_STANDARD, 159, Product::PRICE_TYPE_WEIGHT],
            [self::CAFFE_BARCODE, 'Caffè', VatCategoryFixtures::VAT_CATEGORY_STANDARD, 250, Product::PRICE_TYPE_UNIT],
            [self::LATTE_BARCODE, 'Latte', VatCategoryFixtures::VAT_CATEGORY_STANDARD, 150, Product::PRICE_TYPE_UNIT],
            [self::UOVA_BARCODE, 'Uova', VatCategoryFixtures::VAT_CATEGORY_STANDARD, 300, Product::PRICE_TYPE_UNIT],
            [self::POMODORI_BARCODE, 'Pomodori', VatCategoryFixtures::VAT_CATEGORY_STANDARD, 99, Product::PRICE_TYPE_UNIT],
            [self::PROSCIUTTO_BARCODE, 'Prosciutto', VatCategoryFixtures::VAT_CATEGORY_STANDARD, 200, Product::PRICE_TYPE_UNIT],
            [self::SOTTILETTE_BARCODE, 'Sottilette', VatCategoryFixtures::VAT_CATEGORY_STANDARD, 100, Product::PRICE_TYPE_UNIT],
            [self::POLENTA_BARCODE, 'Polenta', VatCategoryFixtures::VAT_CATEGORY_STANDARD, 230, Product::PRICE_TYPE_UNIT],
        ];
    }


}
