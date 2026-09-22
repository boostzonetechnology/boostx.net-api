<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Blog;
use App\Repository\BlogCategoryRepository;
use App\Repository\BlogRepository;
use App\Support\Slugger;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use RuntimeException;

final class BlogService
{
    public function __construct(
        private readonly BlogRepository $blogs,
        private readonly BlogCategoryRepository $categories,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function listPublished(): array
    {
        return array_map(static fn (Blog $b) => $b->toArray(), $this->blogs->findPublished());
    }

    /** @return array<string, mixed> */
    public function getPublishedBySlug(string $slug): array
    {
        $blog = $this->blogs->findPublishedBySlug($slug);
        if (!$blog) {
            throw new RuntimeException('Post not found.', 404);
        }

        return $blog->toArray();
    }

    /** @return list<array<string, mixed>> */
    public function listAdmin(): array
    {
        return array_map(static fn (Blog $b) => $b->toArray(), $this->blogs->findAllAdmin());
    }

    /** @return array<string, mixed> */
    public function getAdmin(int $id): array
    {
        $blog = $this->blogs->findAdminById($id);
        if (!$blog) {
            throw new RuntimeException('Post not found.', 404);
        }

        return $blog->toArray();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): array
    {
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            throw new InvalidArgumentException('Title is required.');
        }

        $categoryId = (int) ($data['category_id'] ?? 0);
        $category = $this->categories->find($categoryId);
        if (!$category) {
            throw new InvalidArgumentException('Valid category_id is required.');
        }

        $slugInput = trim((string) ($data['slug'] ?? ''));
        $slug = $this->uniqueSlug($slugInput !== '' ? Slugger::slugify($slugInput) : Slugger::slugify($title));
        $content = $this->encodeContent($data['content'] ?? ['blocks' => []]);
        $postAt = $this->parseDate((string) ($data['post_at'] ?? 'now'));

        $blog = new Blog($title, $slug, $content, $category, $postAt);
        $this->applyFields($blog, $data);
        $this->blogs->save($blog);

        return $blog->toArray();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): array
    {
        $blog = $this->blogs->findAdminById($id);
        if (!$blog) {
            throw new RuntimeException('Post not found.', 404);
        }

        if (array_key_exists('title', $data)) {
            $title = trim((string) $data['title']);
            if ($title === '') {
                throw new InvalidArgumentException('Title is required.');
            }
            $blog->setTitle($title);
        }

        if (array_key_exists('category_id', $data)) {
            $category = $this->categories->find((int) $data['category_id']);
            if (!$category) {
                throw new InvalidArgumentException('Valid category_id is required.');
            }
            $blog->setCategory($category);
        }

        if (array_key_exists('slug', $data) || array_key_exists('title', $data)) {
            $slugInput = trim((string) ($data['slug'] ?? ''));
            $base = $slugInput !== '' ? Slugger::slugify($slugInput) : Slugger::slugify($blog->getTitle());
            $blog->setSlug($this->uniqueSlug($base, $blog->getId()));
        }

        if (array_key_exists('content', $data)) {
            $blog->setContent($this->encodeContent($data['content']));
        }

        if (array_key_exists('post_at', $data)) {
            $blog->setPostAt($this->parseDate((string) $data['post_at']));
        }

        $this->applyFields($blog, $data);
        $this->em->flush();

        return $blog->toArray();
    }

    public function delete(int $id): void
    {
        $blog = $this->blogs->findAdminById($id);
        if (!$blog) {
            throw new RuntimeException('Post not found.', 404);
        }

        $this->blogs->remove($blog);
    }

    /** @param array<string, mixed> $data */
    private function applyFields(Blog $blog, array $data): void
    {
        if (array_key_exists('excerpt', $data)) {
            $blog->setExcerpt($data['excerpt'] !== null ? (string) $data['excerpt'] : null);
        }
        if (array_key_exists('featured_image', $data)) {
            $blog->setFeaturedImage($data['featured_image'] !== null ? (string) $data['featured_image'] : null);
        }
        if (array_key_exists('author', $data)) {
            $author = trim((string) $data['author']);
            $blog->setAuthor($author !== '' ? $author : 'boostX Team');
        }
        if (array_key_exists('tags', $data)) {
            $tags = $data['tags'];
            if (is_array($tags)) {
                $blog->setTags(implode(',', array_map('strval', $tags)));
            } else {
                $blog->setTags((string) $tags);
            }
        }
        if (array_key_exists('seo_title', $data)) {
            $blog->setSeoTitle($data['seo_title'] !== null ? (string) $data['seo_title'] : null);
        }
        if (array_key_exists('seo_description', $data)) {
            $blog->setSeoDescription($data['seo_description'] !== null ? (string) $data['seo_description'] : null);
        }
        if (array_key_exists('published', $data)) {
            $blog->setPublished((bool) $data['published']);
        }
        if (array_key_exists('lang', $data)) {
            $lang = trim((string) $data['lang']);
            $blog->setLang($lang !== '' ? $lang : 'en');
        }
    }

    private function encodeContent(mixed $content): string
    {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $content = $decoded;
            } else {
                $content = ['blocks' => [['id' => 't1', 'type' => 'text', 'html' => $content]]];
            }
        }

        if (!is_array($content)) {
            $content = ['blocks' => []];
        }

        if (!isset($content['blocks']) || !is_array($content['blocks'])) {
            $content = ['blocks' => []];
        }

        return json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{"blocks":[]}';
    }

    private function parseDate(string $value): DateTimeImmutable
    {
        $value = trim(str_replace('T', ' ', $value));
        if ($value === '' || $value === 'now') {
            return new DateTimeImmutable();
        }

        try {
            return new DateTimeImmutable($value);
        } catch (\Exception) {
            throw new InvalidArgumentException('Invalid post_at datetime.');
        }
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $n = 2;
        while ($this->blogs->slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $n;
            $n++;
        }

        return $slug;
    }
}
