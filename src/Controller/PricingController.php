<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PricingService;
use App\Support\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class PricingController
{
    public function __construct(
        private readonly PricingService $pricing,
    ) {
    }

    public function index(Request $request, Response $response): Response
    {
        return JsonResponse::ok($response, $this->pricing->forRequest($request));
    }
}
