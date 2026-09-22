<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BlogRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlogRepository::class)]
#[ORM\Table(name: 'blogs')]
#[ORM\UniqueConstraint(name: 'uniq_blogs_slug', columns: ['slug'])]
class Blog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(length: 255)]
    private string $slug;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $excerpt = null;

    #[ORM\Column(name: 'featured_image', length: 500, nullable: true)]
    private ?string $featuredImage = null;

    #[ORM\Column(length: 120)]
    private string $author = 'boostX Team';

    #[ORM\Column(name: 'post_at')]
    private DateTimeImmutable $postAt;

    #[ORM\ManyToOne(targetEntity: BlogCategory::class)]
    #[ORM\JoinColumn(name: 'category_id', referencedColumnName: 'id', nullable: false)]
    private BlogCategory $category;

    #[ORM\Column(length: 255)]
    private string $tags = '';

    #[ORM\Column(name: 'seo_title', length: 255, nullable: true)]
    private ?string $seoTitle = null;

    #[ORM\Column(name: 'seo_description', length: 320, nullable: true)]
    private ?string $seoDescription = null;

    #[ORM\Column(type: 'boolean')]
    private bool $published = false;

    #[ORM\Column(length: 8)]
    private string $lang = 'en';

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'updated_at')]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $title,
        string $slug,
        string $content,
        BlogCategory $category,
        DateTimeImmutable $postAt,
    ) {
        $now = new DateTimeImmutable();
        $this->title = $title;
        $this->slug = $slug;
        $this->content = $content;
        $this->category = $category;
        $this->postAt = $postAt;
        $this->createdAt = $now;
        $this->updatedAt = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
        $this->touch();
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
        $this->touch();
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): void
    {
        $this->excerpt = $excerpt;
        $this->touch();
    }

    public function getFeaturedImage(): ?string
    {
        return $this->featuredImage;
    }

    public function setFeaturedImage(?string $featuredImage): void
    {
        $this->featuredImage = $featuredImage;
        $this->touch();
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
        $this->touch();
    }

    public function getPostAt(): DateTimeImmutable
    {
        return $this->postAt;
    }

    public function setPostAt(DateTimeImmutable $postAt): void
    {
        $this->postAt = $postAt;
        $this->touch();
    }

    public function getCategory(): BlogCategory
    {
        return $this->category;
    }

    public function setCategory(BlogCategory $category): void
    {
        $this->category = $category;
        $this->touch();
    }

    public function getTags(): string
    {
        return $this->tags;
    }

    public function setTags(string $tags): void
    {
        $this->tags = $tags;
        $this->touch();
    }

    public function getSeoTitle(): ?string
    {
        return $this->seoTitle;
    }

    public function setSeoTitle(?string $seoTitle): void
    {
        $this->seoTitle = $seoTitle;
        $this->touch();
    }

    public function getSeoDescription(): ?string
    {
        return $this->seoDescription;
    }

    public function setSeoDescription(?string $seoDescription): void
    {
        $this->seoDescription = $seoDescription;
        $this->touch();
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
        $this->touch();
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
        $this->touch();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /** @return array{blocks: list<array<string, mixed>>} */
    public function decodeContent(): array
    {
        $data = json_decode($this->content, true);
        if (!is_array($data) || !isset($data['blocks']) || !is_array($data['blocks'])) {
            return ['blocks' => [['id' => 't1', 'type' => 'text', 'html' => $this->content]]];
        }

        return $data;
    }

    /** @return list<string> */
    public function tagsList(): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->tags))));
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->decodeContent(),
            'excerpt' => $this->excerpt,
            'featured_image' => $this->featuredImage,
            'author' => $this->author,
            'post_at' => $this->postAt->format('Y-m-d H:i:s'),
            'category_id' => $this->category->getId(),
            'category' => $this->category->toArray(),
            'tags' => $this->tagsList(),
            'seo_title' => $this->seoTitle,
            'seo_description' => $this->seoDescription,
            'published' => $this->published,
            'lang' => $this->lang,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }

    private function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
