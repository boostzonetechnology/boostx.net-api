<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Blog;
use App\Entity\SeoPage;
use App\Repository\BlogRepository;
use App\Repository\SeoPageRepository;
use App\Repository\SeoSettingRepository;
use App\Support\Env;

final class SeoService
{
    public const SETTING_LLMS_INTRO = 'llms_intro';

    private const DEFAULT_LLMS_INTRO = 'The restaurant operating system for next-gen restaurants. POS, online ordering, inventory, kitchen display, riders, analytics, and more — 0% commission.';

    public function __construct(
        private readonly SeoPageRepository $pages,
        private readonly SeoSettingRepository $settings,
        private readonly BlogRepository $blogs,
    ) {
    }

    public function robotsTxt(): string
    {
        $siteUrl = $this->siteUrl();
        $lines = ['User-agent: *'];

        if ($this->isProduction()) {
            $lines[] = 'Allow: /';
            $lines[] = 'Disallow: /super-admin';
            foreach ($this->pages->findDisabledOrdered() as $page) {
                if ($page->getPath() === '/') {
                    continue;
                }
                $lines[] = 'Disallow: ' . $page->getPath();
            }
        } else {
            $lines[] = 'Disallow: /';
        }

        $lines[] = '';
        $lines[] = 'Sitemap: ' . $siteUrl . '/sitemap.xml';
        $lines[] = '';

        return implode("\n", $lines);
    }

    public function sitemapXml(): string
    {
        $siteUrl = $this->siteUrl();
        $xml = [];
        $xml[] = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($this->pages->findEnabledOrdered() as $page) {
            $loc = $this->absoluteUrl($siteUrl, $page->getPath());
            $xml[] = '  <url>';
            $xml[] = '    <loc>' . $this->escapeXml($loc) . '</loc>';
            $xml[] = '  </url>';
        }

        foreach ($this->blogs->findPublished() as $blog) {
            /** @var Blog $blog */
            $loc = $this->absoluteUrl($siteUrl, '/blog/' . $blog->getSlug());
            $xml[] = '  <url>';
            $xml[] = '    <loc>' . $this->escapeXml($loc) . '</loc>';
            $xml[] = '    <lastmod>' . $blog->getUpdatedAt()->format('Y-m-d') . '</lastmod>';
            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';
        $xml[] = '';

        return implode("\n", $xml);
    }

    public function llmsTxt(): string
    {
        $siteUrl = $this->siteUrl();
        $intro = trim($this->settings->getValue(self::SETTING_LLMS_INTRO, self::DEFAULT_LLMS_INTRO));
        if ($intro === '') {
            $intro = self::DEFAULT_LLMS_INTRO;
        }

        $lines = [
            '# boostX',
            '',
            '> ' . $intro,
            '',
        ];

        $bySection = [];
        foreach ($this->pages->findEnabledOrdered() as $page) {
            $bySection[$page->getSection()][] = $page;
        }

        foreach ($bySection as $section => $pages) {
            $lines[] = '## ' . $section;
            $lines[] = '';
            foreach ($pages as $page) {
                /** @var SeoPage $page */
                $url = $this->absoluteUrl($siteUrl, $page->getPath());
                $lines[] = '- [' . $page->getTitle() . '](' . $url . '): ' . $page->getDescription();
            }
            $lines[] = '';
        }

        $posts = $this->blogs->findPublished();
        $lines[] = '## Blog';
        $lines[] = '';
        if ($posts === []) {
            $lines[] = '- No published articles yet.';
        } else {
            foreach ($posts as $blog) {
                /** @var Blog $blog */
                $url = $this->absoluteUrl($siteUrl, '/blog/' . $blog->getSlug());
                $lines[] = '- [' . $blog->getTitle() . '](' . $url . ')';
            }
        }
        $lines[] = '';
        $lines[] = '## Notes';
        $lines[] = '';
        $lines[] = '- Do not index `/super-admin` (private CMS).';
        $lines[] = '';

        return implode("\n", $lines);
    }

    private function siteUrl(): string
    {
        return rtrim((string) Env::get('SITE_URL', (string) Env::get('APP_URL', 'https://boostx.net')), '/');
    }

    private function isProduction(): bool
    {
        return strtolower((string) Env::get('APP_ENV', 'local')) === 'production';
    }

    private function absoluteUrl(string $siteUrl, string $path): string
    {
        if ($path === '/' || $path === '') {
            return $siteUrl . '/';
        }

        return $siteUrl . '/' . ltrim($path, '/');
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
