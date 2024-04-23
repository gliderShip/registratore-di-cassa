<?php

/* (c) Erin Hima <erinhima@gmail.com> */

namespace App\Entity;

use App\Service\PriceFormater;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReceiptRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReceiptRepository::class)]
class Receipt
{
    public const STATUS_OPEN = 'OPEN';
    public const STATUS_CLOSED = 'CLOSED';

    public const STATUSES = [
        self::STATUS_OPEN => self::STATUS_OPEN,
        self::STATUS_CLOSED => self::STATUS_CLOSED,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** @var Collection<ReceiptLine> */
    #[ORM\OneToMany(targetEntity: ReceiptLine::class, mappedBy: 'receipt', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Valid]
    private Collection $receiptLines;

    #[ORM\Column(type: 'string', length: 31, options: ['default' => self::STATUS_OPEN])]
    private string $status = self::STATUS_OPEN;

    public function __construct()
    {
        $this->receiptLines = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /** @return Collection<ReceiptLine> */
    public function getReceiptLines(): Collection
    {
        return $this->receiptLines;
    }

    public function addReceiptLine(ReceiptLine $receiptLine): static
    {
        if (!$this->receiptLines->contains($receiptLine)) {
            $this->receiptLines->add($receiptLine);
            $receiptLine->setReceipt($this);
        }

        return $this;
    }

    public function removeReceiptLine(ReceiptLine $receiptLine): static
    {
        if ($this->receiptLines->removeElement($receiptLine)) {
            // set the owning side to null (unless already changed)
            if ($receiptLine->getReceipt() === $this) {
                $receiptLine->setReceipt(null);
            }
        }

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isClosed(): bool
    {
        return self::STATUS_CLOSED === $this->status;
    }

    public function getReceiptLine(Product $product): ?ReceiptLine
    {
        foreach ($this->getReceiptLines() as $receiptLine) {
            if ($receiptLine->getProductCode() === $product->getBarCode()) {
                return $receiptLine;
            }
        }

        return null;
    }

    /** @return array<string, Product> */
    public function getProducts(): array
    {
        $products = [];
        foreach ($this->getReceiptLines() as $receiptLine) {
            $product = $receiptLine->getProduct();
            $products[$product->getBarCode()] = $product;
        }

        return $products;
    }

    /** @return array<string, int> */
    public function getProductQuantities(): array
    {
        $productQuantities = [];
        foreach ($this->getReceiptLines() as $receiptLine) {
            $product = $receiptLine->getProduct();
            $quantity = $receiptLine->getQuantity();
            $productQuantities[$product->getBarCode()] = $quantity;
        }

        return $productQuantities;
    }

    public function hasProduct(Product $product): bool
    {
        $products = $this->getProducts();

        return isset($products[$product->getBarCode()]);
    }

    /** @param Product[] $products */
    public function hasBundleProducts(array $products): bool
    {
        $productQuantities = $this->getProductQuantities();

        foreach ($products as $product) {
            $productCode = $product->getBarCode();
            if (!isset($productQuantities[$productCode]) || $productQuantities[$productCode] <= 0) {
                return false;
            } else {
                --$productQuantities[$productCode];
            }
        }

        return true;
    }

    public function addProductDiscount(Product $product, ReceiptLineDiscount $discount): void
    {
        $receiptLine = $this->getReceiptLine($product);
        if (!$receiptLine) {
            throw new \InvalidArgumentException(sprintf('Product with barcode "%s" not found in receipt', $product->getBarCode()));
        }

        $receiptLine->addDiscount($discount);
    }

    /** @retrun arrray<string, int> * */
    public function getProductsByQuantity(): array
    {
        $productsByQuantity = [];

        foreach ($this->getReceiptLines() as $receiptLine) {
            $productCode = $receiptLine->getProductCode();
            $productsByQuantity[$productCode] = $receiptLine->getQuantity();
        }

        return $productsByQuantity;
    }

    public function getProductQuantity(): float
    {
        $quantity = 0;
        foreach ($this->getReceiptLines() as $receiptLine) {
            $quantity += $receiptLine->getQuantity();
        }

        return $quantity;
    }

    public function getNetAmount(): float
    {
        $netAmount = 0;
        foreach ($this->getReceiptLines() as $receiptLine) {
            $netAmount += $receiptLine->getNetAmount();
        }

        return $netAmount;
    }

    public function getDiscountAmount(): float
    {
        $discountAmount = 0;
        foreach ($this->getReceiptLines() as $receiptLine) {
            $discountAmount += $receiptLine->getDiscountAmount();
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
        $discounts = '';

        foreach ($this->getReceiptLines() as $receiptLine) {
            foreach ($receiptLine->getDiscounts() as $discount) {
                $discounts .= $discount->getName() . ' ' . PriceFormater::format($discount->getAbsoluteAmount()) . PHP_EOL;
            }
        }

        $receipt = [
            'Items' => $this->getReceiptLines()->count(),
            'Quantity' => $this->getProductQuantity(),
            'Net Amount' => PriceFormater::format($this->getNetAmount()),
            'Discount Amount' => PriceFormater::format($this->getDiscountAmount()),
            'Amount' => PriceFormater::format($this->getAmount()),
        ];

        if ($includeDiscounts) {
            $receipt['Discounts'] = $discounts;
        }

        return $receipt;
    }
}
