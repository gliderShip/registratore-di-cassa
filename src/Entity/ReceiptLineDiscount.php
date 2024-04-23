<?php

namespace App\Entity;

use App\Trait\DiscountTrait;
use Doctrine\ORM\Mapping as ORM;
use App\Interface\DiscountInterface;
use App\Repository\ReceiptLineDiscountRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReceiptLineDiscountRepository::class)]
class ReceiptLineDiscount implements DiscountInterface
{
    use DiscountTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 63, unique: false)]
    #[Assert\NotBlank(allowNull: false)]
    private ?string $name;

    #[ORM\ManyToOne(inversedBy: 'discounts')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull()]
    private ?ReceiptLine $receiptLine = null;

    public function __construct(?string $name = null, ?string $type = null, ?int $amount = 0)
    {
        $this->name = $name;
        $this->type = $type ?? self::TYPE_FIXED;
        $this->amount = (int)$amount;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReceiptLine(): ?ReceiptLine
    {
        return $this->receiptLine;
    }

    public function setReceiptLine(?ReceiptLine $receiptLine): static
    {
        $this->receiptLine = $receiptLine;

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
