<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\SeoPage;
use App\Repository\SeoPageRepository;
use App\Repository\SeoSettingRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use RuntimeException;

final class AdminSeoService
{
    public function __construct(
        private readonly SeoPageRepository $pages,
        private readonly SeoSettingRepository $settings,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function listPages(): array
    {
        return array_map(
            static fn (SeoPage $p) => $p->toArray(),
            $this->pages->findAllOrdered()
        );
    }

    /** @return array<string, mixed> */
    public function getPage(int $id): array
    {
        $page = $this->pages->find($id);
        if (!$page) {
            throw new RuntimeException('SEO page not found.', 404);
        }

        return $page->toArray();
    }

    /** @param array<string, mixed> $data */
    public function createPage(array $data): array
    {
        $path = SeoPage::normalizePath((string) ($data['path'] ?? ''));
        $this->assertPublicPath($path);

        if ($this->pages->findByPath($path)) {
            throw new InvalidArgumentException('Path already exists.');
        }

        $title = trim((string) ($data['title'] ?? ''));
        $description = trim((string) ($data['description'] ?? ''));
        $section = trim((string) ($data['section'] ?? 'Company'));
        if ($title === '' || $description === '' || $section === '') {
            throw new InvalidArgumentException('Title, description, and section are required.');
        }

        $enabled = array_key_exists('is_enabled', $data) ? (bool) $data['is_enabled'] : true;
        $sort = (int) ($data['sort_order'] ?? $this->nextSortOrder());

        $page = new SeoPage($path, $title, $description, $section, $sort, $enabled);
        $this->pages->save($page);

        return $page->toArray();
    }

    /** @param array<string, mixed> $data */
    public function updatePage(int $id, array $data): array
    {
        $page = $this->pages->find($id);
        if (!$page) {
            throw new RuntimeException('SEO page not found.', 404);
        }

        if (array_key_exists('path', $data)) {
            $path = SeoPage::normalizePath((string) $data['path']);
            $this->assertPublicPath($path);
            $existing = $this->pages->findByPath($path);
            if ($existing && $existing->getId() !== $page->getId()) {
                throw new InvalidArgumentException('Path already exists.');
            }
            $page->setPath($path);
        }

        if (array_key_exists('title', $data)) {
            $title = trim((string) $data['title']);
            if ($title === '') {
                throw new InvalidArgumentException('Title is required.');
            }
            $page->setTitle($title);
        }

        if (array_key_exists('description', $data)) {
            $description = trim((string) $data['description']);
            if ($description === '') {
                throw new InvalidArgumentException('Description is required.');
            }
            $page->setDescription($description);
        }

        if (array_key_exists('section', $data)) {
            $section = trim((string) $data['section']);
            if ($section === '') {
                throw new InvalidArgumentException('Section is required.');
            }
            $page->setSection($section);
        }

        if (array_key_exists('is_enabled', $data)) {
            $page->setIsEnabled((bool) $data['is_enabled']);
        }

        if (array_key_exists('sort_order', $data)) {
            $page->setSortOrder((int) $data['sort_order']);
        }

        $this->em->flush();

        return $page->toArray();
    }

    public function deletePage(int $id): void
    {
        $page = $this->pages->find($id);
        if (!$page) {
            throw new RuntimeException('SEO page not found.', 404);
        }
        $this->pages->remove($page);
    }

    /** @return array{llms_intro: string} */
    public function getSettings(): array
    {
        return [
            'llms_intro' => $this->settings->getValue(
                SeoService::SETTING_LLMS_INTRO,
                'The restaurant operating system for next-gen restaurants. POS, online ordering, inventory, kitchen display, riders, analytics, and more — 0% commission.'
            ),
        ];
    }

    /** @param array<string, mixed> $data */
    public function updateSettings(array $data): array
    {
        if (array_key_exists('llms_intro', $data)) {
            $intro = trim((string) $data['llms_intro']);
            $this->settings->setValue(SeoService::SETTING_LLMS_INTRO, $intro);
        }

        return $this->getSettings();
    }

    private function assertPublicPath(string $path): void
    {
        if (str_starts_with($path, '/super-admin')) {
            throw new InvalidArgumentException('Admin paths cannot be added to SEO pages.');
        }
    }

    private function nextSortOrder(): int
    {
        $rows = $this->pages->findAllOrdered();
        if ($rows === []) {
            return 10;
        }
        $last = $rows[array_key_last($rows)];

        return $last->getSortOrder() + 10;
    }
}
