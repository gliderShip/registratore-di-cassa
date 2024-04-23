<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Trait\DiscountTrait;
use App\Interface\DiscountInterface;
use App\Repository\ProductDiscountRuleRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: ProductDiscountRuleRepository::class)]
#[UniqueEntity('name')]
class ProductDiscountRule implements DiscountInterface
{
    use DiscountTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'productDiscountRules')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Product $product = null;

    #[ORM\ManyToOne(inversedBy: 'productDiscountRules')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?DiscountRule $discountRule = null;


    public function __construct(?string $name = null, ?string $type = null, ?int $amount = null)
    {
        $this->name = $name;
        $this->type = $type ?? self::TYPE_FIXED;
        $this->amount = (int)$amount;
    }

    #[Assert\Callback()]
    public function validate(ExecutionContextInterface $context, mixed $payload): void
    {
        switch ($this->type) {
            case self::TYPE_PERCENTAGE:
                if ($this->amount < 0 || $this->amount > 100) {
                    $context->buildViolation('The percentage value must be between 0 and 100.')
                        ->atPath('amount')
                        ->addViolation();
                }
                break;
            case self::TYPE_FIXED:
                if ($this->amount < 0 || $this->amount > $this->getProduct()->getListPriceAmount()) {
                    $context->buildViolation('The fixed value must be between 0 and the list price amount.')
                        ->atPath('amount')
                        ->addViolation();
                }
                break;
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }

    public function getDiscountRule(): ?DiscountRule
    {
        return $this->discountRule;
    }

    public function setDiscountRule(?DiscountRule $discountRule): static
    {
        $this->discountRule = $discountRule;

        return $this;
    }
}
