<?php

/* (c) Erin Hima <erihima@gmail.com> */

namespace App\Entity;

use App\Trait\DiscountTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Interface\DiscountInterface;
use App\Repository\DiscountRuleRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: DiscountRuleRepository::class)]
#[UniqueEntity('name')]
class DiscountRule implements DiscountInterface
{
    use DiscountTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** @var Collection<ProductDiscountRule> */
    #[ORM\OneToMany(targetEntity: ProductDiscountRule::class, mappedBy: 'discountRule', orphanRemoval: true)]
    private Collection $productDiscountRules;

    public function __construct(?string $name = null, ?string $type = null, ?int $amount = null)
    {
        $this->name = $name;
        $this->type = $type ?? self::TYPE_FIXED;
        $this->amount = (int)$amount;

        $this->productDiscountRules = new ArrayCollection();
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
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /** @return Collection<ProductDiscountRule> */
    public function getProductDiscountRules(): Collection
    {
        return $this->productDiscountRules;
    }

    public function addProductDiscountRule(ProductDiscountRule $productDiscount): static
    {
        if (!$this->productDiscountRules->contains($productDiscount)) {
            $this->productDiscountRules->add($productDiscount);
            $productDiscount->setDiscountRule($this);
        }

        return $this;
    }

    public function removeProductDiscountRule(ProductDiscountRule $productDiscount): static
    {
        if ($this->productDiscountRules->removeElement($productDiscount)) {
            // set the owning side to null (unless already changed)
            if ($productDiscount->getDiscountRule() === $this) {
                $productDiscount->setDiscountRule(null);
            }
        }

        return $this;
    }

    /** @return array<int, Product> */
    public function getProducts(): array
    {
        $products = [];
        foreach ($this->productDiscountRules as $productDiscountRule) {
            $product = $productDiscountRule->getProduct();
            $products[] = $product;
        }

        return $products;
    }

    public function getProductsListAmountTotal(): int
    {
        $amount = 0;
        foreach ($this->getProducts() as $product) {
            $amount += $product->getListPriceAmount();
        }

        return $amount;
    }

    /** @retrun arrray<string, int> **/
    public function getProductsByQuantity(): array
    {
        $productsByQuantity = [];

        foreach ($this->productDiscountRules as $productDiscountRule) {
            $productCode = $productDiscountRule->getProduct()->getBarCode();
            $quantity = $productsByQuantity[$productCode] ?? 0;
            $productsByQuantity[$productCode] = $quantity + 1;
        }

        return $productsByQuantity;
    }
}
