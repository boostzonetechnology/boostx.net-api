<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\BlogCategory;
use App\Repository\BlogCategoryRepository;
use App\Repository\BlogRepository;
use App\Support\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use RuntimeException;

final class CategoryService
{
    public function __construct(
        private readonly BlogCategoryRepository $categories,
        private readonly BlogRepository $blogs,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return array_map(
            static fn (BlogCategory $c) => $c->toArray(),
            $this->categories->findAllOrdered()
        );
    }

    /** @return array<string, mixed> */
    public function get(int $id): array
    {
        $category = $this->categories->find($id);
        if (!$category) {
            throw new RuntimeException('Category not found.', 404);
        }

        return $category->toArray();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): array
    {
        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Name is required.');
        }

        $slug = trim((string) ($data['slug'] ?? ''));
        $slug = $slug !== '' ? Slugger::slugify($slug) : Slugger::slugify($name);
        $slug = $this->uniqueSlug($slug);

        $category = new BlogCategory($name, $slug);
        $this->categories->save($category);

        return $category->toArray();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): array
    {
        $category = $this->categories->find($id);
        if (!$category) {
            throw new RuntimeException('Category not found.', 404);
        }

        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                throw new InvalidArgumentException('Name is required.');
            }
            $category->setName($name);
        }

        if (array_key_exists('slug', $data) || array_key_exists('name', $data)) {
            $slugInput = trim((string) ($data['slug'] ?? ''));
            $base = $slugInput !== '' ? Slugger::slugify($slugInput) : Slugger::slugify($category->getName());
            $category->setSlug($this->uniqueSlug($base, $category->getId()));
        }

        $this->em->flush();

        return $category->toArray();
    }

    public function delete(int $id): void
    {
        $category = $this->categories->find($id);
        if (!$category) {
            throw new RuntimeException('Category not found.', 404);
        }

        if ($this->blogs->countByCategory($category) > 0) {
            throw new InvalidArgumentException('Cannot delete category while blogs still use it.');
        }

        $this->categories->remove($category);
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $n = 2;
        while ($this->categories->slugExists($slug, $ignoreId)) {
            $slug = $base . '-' . $n;
            $n++;
        }

        return $slug;
    }
}
