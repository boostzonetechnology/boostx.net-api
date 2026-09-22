<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SeoPageRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SeoPageRepository::class)]
#[ORM\Table(name: 'seo_pages')]
#[ORM\UniqueConstraint(name: 'uniq_seo_pages_path', columns: ['path'])]
class SeoPage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $path;

    #[ORM\Column(length: 160)]
    private string $title;

    #[ORM\Column(length: 500)]
    private string $description;

    #[ORM\Column(length: 80)]
    private string $section;

    #[ORM\Column(name: 'is_enabled', type: 'boolean')]
    private bool $isEnabled = true;

    #[ORM\Column(name: 'sort_order', type: 'integer')]
    private int $sortOrder = 0;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at')]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $path,
        string $title,
        string $description,
        string $section,
        int $sortOrder = 0,
        bool $isEnabled = true,
    ) {
        $now = new DateTimeImmutable();
        $this->path = self::normalizePath($path);
        $this->title = $title;
        $this->description = $description;
        $this->section = $section;
        $this->sortOrder = $sortOrder;
        $this->isEnabled = $isEnabled;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public static function normalizePath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path === '/') {
            return '/';
        }
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = self::normalizePath($path);
        $this->touch();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
        $this->touch();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    public function getSection(): string
    {
        return $this->section;
    }

    public function setSection(string $section): void
    {
        $this->section = $section;
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
            'path' => $this->path,
            'title' => $this->title,
            'description' => $this->description,
            'section' => $this->section,
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
