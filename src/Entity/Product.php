<?php

/* (c) Erin Hima <erinhima@gmail.com> */

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ProductRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[UniqueEntity('barCode')]
#[UniqueEntity('name')]
class Product
{
    public const PRICE_TYPE_WEIGHT = 'PRICE_PER_WEIGHT';
    public const PRICE_TYPE_UNIT = 'PRICE_PER_UNIT';

    public const PRICE_TYPES = [
        self::PRICE_TYPE_WEIGHT => self::PRICE_TYPE_WEIGHT,
        self::PRICE_TYPE_UNIT => self::PRICE_TYPE_UNIT,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 13, unique: true, options: ['fixed' => true, 'comment' => 'EAN-13'])]
    #[Assert\Length(min: 13, max: 13)]
    private ?string $barCode = null;

    #[ORM\Column(length: 63, unique: true)]
    #[Assert\Length(max: 63)]
    #[Assert\NotBlank(allowNull: false)]
    private ?string $name;

    #[ORM\ManyToOne(inversedBy: 'products')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ProductVatCategory $vatCategory;

    #[ORM\Column(type: Types::INTEGER, options: ['unsigned' => true, 'comment' => 'net list price in cents'])]
    #[Assert\PositiveOrZero]
    private ?int $listPriceAmount;

    #[ORM\Column(type: Types::STRING, length: 31, options: ['default' => self::PRICE_TYPE_WEIGHT])]
    #[Assert\Choice(choices: self::PRICE_TYPES)]
    private string $listPriceType;

    #[ORM\OneToMany(targetEntity: ReceiptLine::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $orderItems;

    /**
     * @var Collection<int, ProductDiscountRule>
     */
    #[ORM\OneToMany(targetEntity: ProductDiscountRule::class, mappedBy: 'product', orphanRemoval: true)]
    private Collection $productDiscountRules;

    public function __construct(
        ?string $barCode = null,
        ?string $name = null,
        ?ProductVatCategory $vatCategory = null,
        ?int $listPriceAmount = null,
        ?string $listPriceType = null
    ) {
        $this->barCode = $barCode;
        $this->name = $name;
        $this->vatCategory = $vatCategory;
        $this->listPriceAmount = $listPriceAmount;
        $this->listPriceType = $listPriceType ?? self::PRICE_TYPE_WEIGHT;

        $this->orderItems = new ArrayCollection();
        $this->productDiscountRules = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBarCode(): ?string
    {
        return $this->barCode;
    }

    public function setBarCode(string $barCode): static
    {
        $this->barCode = $barCode;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getVatCategory(): ?ProductVatCategory
    {
        return $this->vatCategory;
    }

    public function setVatCategory(?ProductVatCategory $vatCategory): static
    {
        $this->vatCategory = $vatCategory;

        return $this;
    }

    public function getListPriceAmount(): ?int
    {
        return $this->listPriceAmount;
    }

    public function setListPriceAmount(?int $listPriceAmount): void
    {
        $this->listPriceAmount = $listPriceAmount;
    }

    public function getListPriceType(): string
    {
        return $this->listPriceType;
    }

    public function setListPriceType(string $listPriceType): void
    {
        $this->listPriceType = $listPriceType;
    }

    public function canSellByWeight(): bool
    {
        return $this->listPriceType === self::PRICE_TYPE_WEIGHT;
    }

    /**
     * @return Collection<ReceiptLine>
     */
    public function getOrderItems(): Collection
    {
        return $this->orderItems;
    }

    public function addOrderItem(ReceiptLine $orderItem): static
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            $orderItem->setProduct($this);
        }

        return $this;
    }

    public function removeOrderItem(ReceiptLine $orderItem): static
    {
        if ($this->orderItems->removeElement($orderItem)) {
            // set the owning side to null (unless already changed)
            if ($orderItem->getProduct() === $this) {
                $orderItem->setProduct(null);
            }
        }

        return $this;
    }

    /** @return Collection<ProductDiscountRule> */
    public function getProductDiscountRules(): Collection
    {
        return $this->productDiscountRules;
    }

    public function addProductDiscount(ProductDiscountRule $productDiscount): static
    {
        if (!$this->productDiscountRules->contains($productDiscount)) {
            $this->productDiscountRules->add($productDiscount);
            $productDiscount->setProduct($this);
        }

        return $this;
    }

    public function removeProductDiscount(ProductDiscountRule $productDiscount): static
    {
        if ($this->productDiscountRules->removeElement($productDiscount)) {
            // set the owning side to null (unless already changed)
            if ($productDiscount->getProduct() === $this) {
                $productDiscount->setProduct(null);
            }
        }

        return $this;
    }

    /** @return array<int, DiscountRule> */
    public function getDiscountRules(): array
    {
        $discountRules = [];
        foreach ($this->productDiscountRules as $productDiscountRule) {
            $discountRule = $productDiscountRule->getDiscountRule();
            $discountRules[$discountRule->getId()] = $discountRule;
        }

        return $discountRules;
    }

}
