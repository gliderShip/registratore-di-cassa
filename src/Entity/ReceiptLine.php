<?php

/* (c) Erin Hima <erihima@gmail.com> */

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use App\Service\PriceFormater;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReceiptLineRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReceiptLineRepository::class)]
class ReceiptLine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'receiptLines')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Receipt $receipt = null;

    #[ORM\ManyToOne(inversedBy: 'orderItems')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    private ?Product $product;

    #[ORM\Column(type: Types::FLOAT, options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private float $quantity = 1;

    /** @var Collection<ReceiptLineDiscount> */
    #[ORM\OneToMany(targetEntity: ReceiptLineDiscount::class, mappedBy: 'receiptLine', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $discounts;


    public function __construct(?Product $product = null, float $quantity = 1)
    {
        $this->product = $product;
        $this->quantity = (float)$quantity;

        $this->discounts = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReceipt(): ?Receipt
    {
        return $this->receipt;
    }

    public function setReceipt(?Receipt $receipt): static
    {
        $this->receipt = $receipt;

        return $this;
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

    public function getQuantity(): float|int
    {
        return $this->quantity;
    }

    public function setQuantity(float|int $quantity): static
    {
        if (!$this->product) {
            throw new \RuntimeException('Please provide the product first!');
        }

        if (!$this->product->canSellByWeight() && !is_int($quantity)) {
            throw new \RuntimeException('Quantity must be an integer');
        }

        $this->quantity = $quantity;

        return $this;
    }

    public function addQuantity(float|int $quantity): float
    {
        if (!$this->product) {
            throw new \RuntimeException('Please provide the product first!');
        }

        if (!$this->product->canSellByWeight() && !is_int($quantity)) {
            throw new \RuntimeException('Quantity must be an integer');
        }

        $this->quantity += $quantity;
        return $this->quantity;
    }

    public function getProductCode(): ?string
    {
        return $this->product->getBarcode();
    }

    /** @return Collection<ReceiptLineDiscount> */
    public function getDiscounts(): Collection
    {
        return $this->discounts;
    }

    public function addDiscount(ReceiptLineDiscount $discount): static
    {
        if (!$this->discounts->contains($discount)) {
            $this->discounts->add($discount);
            $discount->setReceiptLine($this);
        }

        return $this;
    }

    public function removeDiscount(ReceiptLineDiscount $discount): static
    {
        if ($this->discounts->removeElement($discount)) {
            // set the owning side to null (unless already changed)
            if ($discount->getReceiptLine() === $this) {
                $discount->setReceiptLine(null);
            }
        }

        return $this;
    }

    public function removeDiscounts(): self
    {
        foreach ($this->discounts as $discount) {
            $this->removeDiscount($discount);
        }

        return $this;
    }

    public function getNetAmount(): float
    {
        $netAmount = $this->getProduct()?->getListPriceAmount() * $this->getQuantity();
        return $netAmount;
    }

    public function getDiscountAmount(): float
    {
        $discountAmount = 0;
        foreach ($this->discounts as $discount) {
            $discountAmount += $discount->getAbsoluteAmount();
        }

        return $discountAmount;
    }

    public function getAmount(): float
    {
        $amount = $this->getNetAmount() - $this->getDiscountAmount();
        return $amount;
    }

    public function toArray(bool $includeDiscounts = false): array
    {
        $receiptLine = [
            'Product' => $this->getProduct()->getName(),
            'Quantity' => $this->getQuantity(),
            'Net Amount' => PriceFormater::format($this->getNetAmount()),
            'Discount' => PriceFormater::format($this->getDiscountAmount()),
            'Amount' => PriceFormater::format($this->getAmount()),
        ];

        if ($includeDiscounts) {
            $discounts = '';
            foreach ($this->getDiscounts() as $discount) {
                $discounts .= $discount->__toString() . PHP_EOL;
            }
            $receiptLine['Discounts'] = $discounts;
        }

        return $receiptLine;
    }
}
