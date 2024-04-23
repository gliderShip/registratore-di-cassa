<?php

/* (c) Erin Hima <erihima@gmail.com> */

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use App\Repository\ProductVatCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: ProductVatCategoryRepository::class)]
#[UniqueEntity('name')]
#[UniqueEntity('code')]
class ProductVatCategory
{
    public const STANDARD_VAT = 22; // 22% VAT

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 15, unique: true)]
    private ?string $code;

    #[ORM\Column(type: Types::INTEGER, options: ['unsigned' => true, 'default' => self::STANDARD_VAT])]
    #[Assert\NotNull]
    #[Assert\Range(min: 0, max: 100)]
    private int $percentage;

    #[ORM\Column(length: 31, unique: true)]
    #[Assert\Length(max: 31)]
    #[Assert\NotBlank(allowNull: false)]
    private ?string $name;

    #[ORM\OneToMany(targetEntity: Product::class, mappedBy: 'vatCategory')]
    private Collection $products;

    public function __construct(?string $code = null, ?int $percentage = null, ?string $name = null)
    {
        $this->code = $code;
        $this->percentage = $percentage ?? self::STANDARD_VAT;
        $this->name = $name;
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getPercentage(): ?int
    {
        return $this->percentage;
    }

    public function setPercentage(int $percentage): static
    {
        $this->percentage = $percentage;

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

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setVatCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getVatCategory() === $this) {
                $product->setVatCategory(null);
            }
        }

        return $this;
    }
}
