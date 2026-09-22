<?php

declare(strict_types=1);

namespace App\Controller;

use App\Support\JsonResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class HealthController
{
    public function index(Request $request, Response $response): Response
    {
        return JsonResponse::ok($response, ['status' => 'ok']);
    }
}
