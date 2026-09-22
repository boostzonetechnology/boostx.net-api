<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SeoSettingRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeoSettingRepository::class)]
#[ORM\Table(name: 'seo_settings')]
class SeoSetting
{
    #[ORM\Id]
    #[ORM\Column(name: 'setting_key', length: 120)]
    private string $key;

    #[ORM\Column(name: 'setting_value', type: Types::TEXT)]
    private string $value;

    #[ORM\Column(name: 'updated_at')]
    private DateTimeImmutable $updatedAt;

    public function __construct(string $key, string $value)
    {
        $this->key = $key;
        $this->value = $value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
