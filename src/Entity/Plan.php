<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PlanRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlanRepository::class)]
#[ORM\Table(name: 'plans')]
#[ORM\UniqueConstraint(name: 'uniq_plans_region_slug', columns: ['region_id', 'slug'])]
class Plan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Region::class)]
    #[ORM\JoinColumn(name: 'region_id', referencedColumnName: 'id', nullable: false)]
    private Region $region;

    #[ORM\Column(length: 160)]
    private string $name;

    #[ORM\Column(length: 180)]
    private string $slug;

    #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
    private string $price;

    #[ORM\Column(name: 'billing_period', length: 32)]
    private string $billingPeriod = 'monthly';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $features = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(name: 'is_highlighted', type: 'boolean')]
    private bool $isHighlighted = false;

    #[ORM\Column(name: 'display_price', length: 64, nullable: true)]
    private ?string $displayPrice = null;

    #[ORM\Column(name: 'period_label', length: 32, nullable: true)]
    private ?string $periodLabel = null;

    #[ORM\Column(name: 'is_enabled', type: 'boolean')]
    private bool $isEnabled = true;

    #[ORM\Column(name: 'sort_order', type: 'integer')]
    private int $sortOrder = 0;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at')]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        Region $region,
        string $name,
        string $slug,
        string $price,
        string $billingPeriod = 'monthly',
        ?string $features = null,
        int $sortOrder = 0,
        bool $isEnabled = true,
        ?string $description = null,
        bool $isHighlighted = false,
        ?string $displayPrice = null,
        ?string $periodLabel = null,
    ) {
        $now = new DateTimeImmutable();
        $this->region = $region;
        $this->name = trim($name);
        $this->slug = $slug;
        $this->price = $price;
        $this->billingPeriod = $billingPeriod;
        $this->features = $features;
        $this->description = $description;
        $this->isHighlighted = $isHighlighted;
        $this->displayPrice = $displayPrice;
        $this->periodLabel = $periodLabel;
        $this->sortOrder = $sortOrder;
        $this->isEnabled = $isEnabled;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegion(): Region
    {
        return $this->region;
    }

    public function setRegion(Region $region): void
    {
        $this->region = $region;
        $this->touch();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = trim($name);
        $this->touch();
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->touch();
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function setPrice(string $price): void
    {
        $this->price = $price;
        $this->touch();
    }

    public function getBillingPeriod(): string
    {
        return $this->billingPeriod;
    }

    public function setBillingPeriod(string $billingPeriod): void
    {
        $this->billingPeriod = $billingPeriod;
        $this->touch();
    }

    public function getFeatures(): ?string
    {
        return $this->features;
    }

    public function setFeatures(?string $features): void
    {
        $this->features = $features;
        $this->touch();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    public function isHighlighted(): bool
    {
        return $this->isHighlighted;
    }

    public function setIsHighlighted(bool $isHighlighted): void
    {
        $this->isHighlighted = $isHighlighted;
        $this->touch();
    }

    public function getDisplayPrice(): ?string
    {
        return $this->displayPrice;
    }

    public function setDisplayPrice(?string $displayPrice): void
    {
        $this->displayPrice = $displayPrice;
        $this->touch();
    }

    public function getPeriodLabel(): ?string
    {
        return $this->periodLabel;
    }

    public function setPeriodLabel(?string $periodLabel): void
    {
        $this->periodLabel = $periodLabel;
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

    /** @return list<string> */
    public function featuresList(): array
    {
        if ($this->features === null || trim($this->features) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\r\n|\r|\n/', $this->features) ?: []
        )));
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'region_id' => $this->region->getId(),
            'region' => $this->region->toArray(),
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'billing_period' => $this->billingPeriod,
            'features' => $this->features,
            'description' => $this->description,
            'is_highlighted' => $this->isHighlighted,
            'display_price' => $this->displayPrice,
            'period_label' => $this->periodLabel,
            'is_enabled' => $this->isEnabled,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    /** @return array<string, mixed> */
    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price' => $this->price,
            'display_price' => $this->displayPrice,
            'period' => $this->periodLabel ?? '',
            'desc' => $this->description ?? '',
            'features' => $this->featuresList(),
            'highlight' => $this->isHighlighted,
            'billing_period' => $this->billingPeriod,
            'sort_order' => $this->sortOrder,
        ];
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
