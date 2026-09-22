<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AdminSeoService;
use App\Support\JsonResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

final class AdminSeoController
{
    public function __construct(private readonly AdminSeoService $seo)
    {
    }

    public function pagesIndex(Request $request, Response $response): Response
    {
        return JsonResponse::ok($response, ['pages' => $this->seo->listPages()]);
    }

    public function pagesShow(Request $request, Response $response, string $id): Response
    {
        try {
            return JsonResponse::ok($response, ['page' => $this->seo->getPage((int) $id)]);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), $this->status($e));
        }
    }

    public function pagesStore(Request $request, Response $response): Response
    {
        try {
            $page = $this->seo->createPage((array) $request->getParsedBody());
            return JsonResponse::ok($response, ['page' => $page], 201);
        } catch (InvalidArgumentException $e) {
            return JsonResponse::error($response, $e->getMessage(), 422);
        }
    }

    public function pagesUpdate(Request $request, Response $response, string $id): Response
    {
        try {
            $page = $this->seo->updatePage((int) $id, (array) $request->getParsedBody());
            return JsonResponse::ok($response, ['page' => $page]);
        } catch (InvalidArgumentException $e) {
            return JsonResponse::error($response, $e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), $this->status($e));
        }
    }

    public function pagesDestroy(Request $request, Response $response, string $id): Response
    {
        try {
            $this->seo->deletePage((int) $id);
            return JsonResponse::ok($response, ['ok' => true]);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), $this->status($e));
        }
    }

    public function settingsShow(Request $request, Response $response): Response
    {
        return JsonResponse::ok($response, ['settings' => $this->seo->getSettings()]);
    }

    public function settingsUpdate(Request $request, Response $response): Response
    {
        $settings = $this->seo->updateSettings((array) $request->getParsedBody());
        return JsonResponse::ok($response, ['settings' => $settings]);
    }

    private function status(RuntimeException $e): int
    {
        $code = (int) $e->getCode();

        return $code >= 400 ? $code : 400;
    }
}
