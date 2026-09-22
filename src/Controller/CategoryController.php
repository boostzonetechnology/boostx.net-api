<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CategoryService;
use App\Support\JsonResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

final class CategoryController
{
    public function __construct(private readonly CategoryService $categories)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return JsonResponse::ok($response, ['categories' => $this->categories->list()]);
    }

    public function show(Request $request, Response $response, string $id): Response
    {
        try {
            return JsonResponse::ok($response, [
                'category' => $this->categories->get((int) $id),
            ]);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), $this->status($e));
        }
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $category = $this->categories->create((array) $request->getParsedBody());
            return JsonResponse::ok($response, ['category' => $category], 201);
        } catch (InvalidArgumentException $e) {
            return JsonResponse::error($response, $e->getMessage(), 422);
        }
    }

    public function update(Request $request, Response $response, string $id): Response
    {
        try {
            $category = $this->categories->update((int) $id, (array) $request->getParsedBody());
            return JsonResponse::ok($response, ['category' => $category]);
        } catch (InvalidArgumentException $e) {
            return JsonResponse::error($response, $e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), $this->status($e));
        }
    }

    public function destroy(Request $request, Response $response, string $id): Response
    {
        try {
            $this->categories->delete((int) $id);
            return JsonResponse::ok($response, ['ok' => true]);
        } catch (InvalidArgumentException $e) {
            return JsonResponse::error($response, $e->getMessage(), 422);
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
