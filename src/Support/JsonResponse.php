<?php

declare(strict_types=1);

namespace App\Support;

use Psr\Http\Message\ResponseInterface as Response;

final class JsonResponse
{
    public static function ok(Response $response, mixed $data = [], int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }

    public static function error(Response $response, string $message, int $status = 400, array $extra = []): Response
    {
        return self::ok($response, array_merge(['error' => $message], $extra), $status);
    }
}
