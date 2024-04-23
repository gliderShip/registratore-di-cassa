<?php

namespace App\Interface;

interface DiscountInterface
{
    public const TYPE_PERCENTAGE = 'PERCENTAGE';
    public const TYPE_FIXED = 'FIXED';

    public const TYPES = [
        self::TYPE_PERCENTAGE => self::TYPE_PERCENTAGE,
        self::TYPE_FIXED => self::TYPE_FIXED,
    ];
    public function getName(): ?string;
    public function setName(string $name): static;
    public function getType(): string;
    public function setType(string $type): static;
    public function getAmount(): int;
    public function setAmount(int $amount): static;
}
