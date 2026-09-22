<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RegionRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RegionRepository::class)]
#[ORM\Table(name: 'regions')]
#[ORM\UniqueConstraint(name: 'uniq_regions_country_code', columns: ['country_code'])]
class Region
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(name: 'country_name', length: 120)]
    private string $countryName;

    #[ORM\Column(name: 'country_code', length: 8)]
    private string $countryCode;

    #[ORM\Column(name: 'currency_code', length: 8)]
    private string $currencyCode;

    #[ORM\Column(name: 'currency_icon', length: 32)]
    private string $currencyIcon;

    #[ORM\Column(name: 'is_enabled', type: 'boolean')]
    private bool $isEnabled = true;

    #[ORM\Column(name: 'sort_order', type: 'integer')]
    private int $sortOrder = 0;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at')]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $countryName,
        string $countryCode,
        string $currencyCode,
        string $currencyIcon,
        int $sortOrder = 0,
        bool $isEnabled = true,
    ) {
        $now = new DateTimeImmutable();
        $this->countryName = trim($countryName);
        $this->countryCode = strtoupper(trim($countryCode));
        $this->currencyCode = strtoupper(trim($currencyCode));
        $this->currencyIcon = trim($currencyIcon);
        $this->sortOrder = $sortOrder;
        $this->isEnabled = $isEnabled;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCountryName(): string
    {
        return $this->countryName;
    }

    public function setCountryName(string $countryName): void
    {
        $this->countryName = trim($countryName);
        $this->touch();
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = strtoupper(trim($countryCode));
        $this->touch();
    }

    public function getCurrencyCode(): string
    {
        return $this->currencyCode;
    }

    public function setCurrencyCode(string $currencyCode): void
    {
        $this->currencyCode = strtoupper(trim($currencyCode));
        $this->touch();
    }

    public function getCurrencyIcon(): string
    {
        return $this->currencyIcon;
    }

    public function setCurrencyIcon(string $currencyIcon): void
    {
        $this->currencyIcon = trim($currencyIcon);
        $this->touch();
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function setIsEnabled(bool $isEnabled): void
    {
        $this->isEnabled = $isEnabled;
        $this->touch();
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setSortOrder(int $sortOrder): void
    {
        $this->sortOrder = $sortOrder;
        $this->touch();
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'country_name' => $this->countryName,
            'country_code' => $this->countryCode,
            'currency_code' => $this->currencyCode,
            'currency_icon' => $this->currencyIcon,
            'is_enabled' => $this->isEnabled,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
