<?php

namespace App\Trait;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait DiscountTrait
{
    #[ORM\Column(length: 63, unique: true)]
    #[Assert\NotBlank(allowNull: false)]
    private ?string $name;

    #[ORM\Column(length: 31)]
    #[Assert\Choice(choices: self::TYPES)]
    #[Assert\NotBlank(allowNull: false)]
    private string $type;

    #[ORM\Column(type: Types::INTEGER, options: ['unsigned' => true])]
    #[Assert\PositiveOrZero()]
    #[Assert\NotNull]
    private int $amount = 0;


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAbsoluteAmount(): int
    {
        $absoluteAmount = $this->amount;
        if ($this->type === self::TYPE_PERCENTAGE) {
            $productAmount = $this->receiptLine->getProduct()->getListPriceAmount();
            $absoluteAmount = ceil($productAmount * $this->amount / 100);
        }

        return $absoluteAmount;
    }
}
