<?php

/* (c) Erin Hima <erinhima@gmail.com> */

namespace App\DataFixtures;

use App\Entity\DiscountRule;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class DiscountRuleFixtures extends Fixture
{
    public const DISCOUNT_RULE_BROCCOLI = 'dr_broccoli_from_2€_to_1.29€';
    public const DISCOUNT_RULE_CAFFE = 'dr_caffè_2.50€_second_at_50%';
    public const DISCOUNT_RULE_POMODORI = 'dr_pomodori_0.99€_two_for_1.59€';
    public const DISCOUNT_RULE_SOTTILETTE = 'dr_sottilette_1.0€_to_0.69_if_prosciutto';
    public const DISCOUNT_RULE_POLENTA = 'dr_ploenta_2.30€_to_1.99';

    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->getDiscountRules() as [$name, $type, $amount]) {
            $discountRule = new DiscountRule(name: $name, type: $type, amount: $amount);
            $errors = $this->validator->validate($discountRule);
            if (count($errors) > 0) {
                throw new ValidationFailedException($discountRule, $errors);
            }

            $manager->persist($discountRule);
            $this->addReference($name, $discountRule);
        }

        $manager->flush();
    }

    /** @return array<array{string, string, int}> */
    private function getDiscountRules(): array
    {
        return [
            // $discountRule = [$name, $type, $amount];
            [self::DISCOUNT_RULE_BROCCOLI, DiscountRule::TYPE_FIXED, 0],
            [self::DISCOUNT_RULE_CAFFE, DiscountRule::TYPE_PERCENTAGE, 0],
            [self::DISCOUNT_RULE_POMODORI, DiscountRule::TYPE_FIXED, 0],
            [self::DISCOUNT_RULE_SOTTILETTE, DiscountRule::TYPE_FIXED, 0],
            [self::DISCOUNT_RULE_POLENTA, DiscountRule::TYPE_FIXED, 31],
        ];
    }


}
