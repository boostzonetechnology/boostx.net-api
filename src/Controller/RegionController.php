<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\RegionService;
use App\Support\JsonResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

final class RegionController
{
    public function __construct(private readonly RegionService $regions)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return JsonResponse::ok($response, ['regions' => $this->regions->list()]);
    }

    public function show(Request $request, Response $response, string $id): Response
    {
        try {
            return JsonResponse::ok($response, ['region' => $this->regions->get((int) $id)]);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), $this->status($e));
        }
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $region = $this->regions->create((array) $request->getParsedBody());
            return JsonResponse::ok($response, ['region' => $region], 201);
        } catch (InvalidArgumentException $e) {
            return JsonResponse::error($response, $e->getMessage(), 422);
        }
    }

    public function update(Request $request, Response $response, string $id): Response
    {
        try {
            $region = $this->regions->update((int) $id, (array) $request->getParsedBody());
            return JsonResponse::ok($response, ['region' => $region]);
        } catch (InvalidArgumentException $e) {
            return JsonResponse::error($response, $e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), $this->status($e));
        }
    }

    public function destroy(Request $request, Response $response, string $id): Response
    {
        try {
            $this->regions->delete((int) $id);
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
