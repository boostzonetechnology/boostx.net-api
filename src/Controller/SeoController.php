<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\SeoService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class SeoController
{
    public function __construct(private readonly SeoService $seo)
    {
    }

    public function robots(Request $request, Response $response): Response
    {
        return $this->plain($response, $this->seo->robotsTxt(), 'text/plain; charset=utf-8');
    }

    public function sitemap(Request $request, Response $response): Response
    {
        return $this->plain($response, $this->seo->sitemapXml(), 'application/xml; charset=utf-8');
    }

    public function llms(Request $request, Response $response): Response
    {
        return $this->plain($response, $this->seo->llmsTxt(), 'text/plain; charset=utf-8');
    }

    private function plain(Response $response, string $body, string $contentType): Response
    {
        $response->getBody()->write($body);

        return $response
            ->withHeader('Content-Type', $contentType)
            ->withHeader('Cache-Control', 'public, max-age=300')
            ->withStatus(200);
    }
}
