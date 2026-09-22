<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\BlogService;
use App\Support\JsonResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

final class BlogController
{
    public function __construct(private readonly BlogService $blogs)
    {
    }

    public function publicIndex(Request $request, Response $response): Response
    {
        return JsonResponse::ok($response, ['posts' => $this->blogs->listPublished()]);
    }

    public function publicShow(Request $request, Response $response, string $slug): Response
    {
        try {
            return JsonResponse::ok($response, [
                'post' => $this->blogs->getPublishedBySlug($slug),
            ]);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), $this->status($e));
        }
    }

    public function adminIndex(Request $request, Response $response): Response
    {
        return JsonResponse::ok($response, ['posts' => $this->blogs->listAdmin()]);
    }

    public function adminShow(Request $request, Response $response, string $id): Response
    {
        try {
            return JsonResponse::ok($response, [
                'post' => $this->blogs->getAdmin((int) $id),
            ]);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), $this->status($e));
        }
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $post = $this->blogs->create((array) $request->getParsedBody());
            return JsonResponse::ok($response, ['post' => $post], 201);
        } catch (InvalidArgumentException $e) {
            return JsonResponse::error($response, $e->getMessage(), 422);
        }
    }

    public function update(Request $request, Response $response, string $id): Response
    {
        try {
            $post = $this->blogs->update((int) $id, (array) $request->getParsedBody());
            return JsonResponse::ok($response, ['post' => $post]);
        } catch (InvalidArgumentException $e) {
            return JsonResponse::error($response, $e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), $this->status($e));
        }
    }

    public function destroy(Request $request, Response $response, string $id): Response
    {
        try {
            $this->blogs->delete((int) $id);
            return JsonResponse::ok($response, ['ok' => true]);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), $this->status($e));
        }
    }

    private function status(RuntimeException $e): int
    {
        $code = (int) $e->getCode();

        return $code >= 400 ? $code : 400;
    }
}
