<?php

/* (c) Erin Hima <erinhima@gmail.com> */

namespace App\DataFixtures;

use App\Entity\ProductDiscountRule;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use http\Exception\InvalidArgumentException;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ProductDiscountRuleFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    /** @return string[] */
    public function getDependencies(): array
    {
        return [
            ProductFixtures::class,
            DiscountRuleFixtures::class
        ];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getProductDiscountRules() as [$name, $type, $amount, $productRef, $discountRuleRef]) {
            $productDiscountRule = new ProductDiscountRule(name: $name, type: $type, amount: $amount);

            $product = $this->getReference($productRef);
            if (!$product) {
                throw new InvalidArgumentException(sprintf('Product with reference "%s" not found', $productRef));
            }

            $discountRule = $this->getReference($discountRuleRef);
            if (!$discountRule) {
                throw new InvalidArgumentException(sprintf('DiscountRule with reference "%s" not found', $discountRuleRef));
            }

            $productDiscountRule->setProduct($product);
            $productDiscountRule->setDiscountRule($discountRule);

            $errors = $this->validator->validate($productDiscountRule);
            if (count($errors) > 0) {
                throw new ValidationFailedException($productDiscountRule, $errors);
            }

            $manager->persist($productDiscountRule);
            $this->addReference($name, $productDiscountRule);
        }

        $manager->flush();
    }

    /** @return array<array{string, string, int}> */
    private function getProductDiscountRules(): array
    {
        return [
            // $productDiscountRule = [$name, $type, $amount, $productRef, $discountRuleRef];
            [
                'pdr_broccoli_discount_71_cent',
                ProductDiscountRule::TYPE_FIXED,
                71,
                ProductFixtures::BROCCOLI_BARCODE,
                DiscountRuleFixtures::DISCOUNT_RULE_BROCCOLI
            ],
            [
                'pdr_caffè_prima_confezione',
                ProductDiscountRule::TYPE_FIXED,
                0,
                ProductFixtures::CAFFE_BARCODE,
                DiscountRuleFixtures::DISCOUNT_RULE_CAFFE
            ],
            [
                'pdr_caffè_seconda_confezione_discount_50%',
                ProductDiscountRule::TYPE_PERCENTAGE,
                50,
                ProductFixtures::CAFFE_BARCODE,
                DiscountRuleFixtures::DISCOUNT_RULE_CAFFE
            ],
            [
                'pdr_pomodori_discount_39',
                ProductDiscountRule::TYPE_FIXED,
                39,
                ProductFixtures::POMODORI_BARCODE,
                DiscountRuleFixtures::DISCOUNT_RULE_POMODORI
            ],
            [
                'pdr_pomodori_discount_0',
                ProductDiscountRule::TYPE_FIXED,
                0,
                ProductFixtures::POMODORI_BARCODE,
                DiscountRuleFixtures::DISCOUNT_RULE_POMODORI
            ],
            [
                'pdr_sottilette_disconut_31_cent',
                ProductDiscountRule::TYPE_FIXED,
                31,
                ProductFixtures::SOTTILETTE_BARCODE,
                DiscountRuleFixtures::DISCOUNT_RULE_SOTTILETTE
            ],
            [
                'pdr_prosciutto_sottiletta',
                ProductDiscountRule::TYPE_FIXED,
                0,
                ProductFixtures::PROSCIUTTO_BARCODE,
                DiscountRuleFixtures::DISCOUNT_RULE_SOTTILETTE
            ],
            [
                'pdr_polenta_discount',
                ProductDiscountRule::TYPE_FIXED,
                0,
                ProductFixtures::POLENTA_BARCODE,
                DiscountRuleFixtures::DISCOUNT_RULE_POLENTA
            ],
        ];
    }

}
