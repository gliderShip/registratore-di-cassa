<?php

/* (c) Erin Hima <erinhima@gmail.com> */

namespace App\DataFixtures;

use App\Entity\ProductVatCategory;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class VatCategoryFixtures extends Fixture
{
    public const VAT_CATEGORY_ZERO = 'VC-000';
    public const VAT_CATEGORY_REDUCED = 'VC-RDC';
    public const VAT_CATEGORY_STANDARD = 'VC-STD';

    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getVatData() as [$code, $percentage, $name]) {
            $vatCategory = new ProductVatCategory(code: $code, percentage: $percentage, name: $name);
            $errors = $this->validator->validate($vatCategory);
            if (count($errors) > 0) {
                throw new ValidationFailedException($vatCategory, $errors);
            }

            $manager->persist($vatCategory);
            $this->addReference($code, $vatCategory);
        }

        $manager->flush();
    }

    /** @return array<array{string, int, string}> */
    private function getVatData(): array
    {
        return [
            // $vatCategory = [$code, $percentage, $name];
            [self::VAT_CATEGORY_ZERO, 0, 'Excluded from VAT'],
            [self::VAT_CATEGORY_REDUCED, 10, 'Reduced VAT'],
            [self::VAT_CATEGORY_STANDARD, 22, 'Standard VAT'],
        ];
    }


}
