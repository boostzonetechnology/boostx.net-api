<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PlanService;
use App\Support\JsonResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

final class PlanController
{
    public function __construct(private readonly PlanService $plans)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return JsonResponse::ok($response, ['plans' => $this->plans->list()]);
    }

    public function show(Request $request, Response $response, string $id): Response
    {
        try {
            return JsonResponse::ok($response, ['plan' => $this->plans->get((int) $id)]);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), $this->status($e));
        }
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $plan = $this->plans->create((array) $request->getParsedBody());
            return JsonResponse::ok($response, ['plan' => $plan], 201);
        } catch (InvalidArgumentException $e) {
            return JsonResponse::error($response, $e->getMessage(), 422);
        }
    }

    public function update(Request $request, Response $response, string $id): Response
    {
        try {
            $plan = $this->plans->update((int) $id, (array) $request->getParsedBody());
            return JsonResponse::ok($response, ['plan' => $plan]);
        } catch (InvalidArgumentException $e) {
            return JsonResponse::error($response, $e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), $this->status($e));
        }
    }

    public function destroy(Request $request, Response $response, string $id): Response
    {
        try {
            $this->plans->delete((int) $id);
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
