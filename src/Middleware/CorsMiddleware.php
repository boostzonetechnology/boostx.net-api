<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

final class CorsMiddleware implements MiddlewareInterface
{
    /** @param list<string> $allowedOrigins */
    public function __construct(private readonly array $allowedOrigins)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $origin = $request->getHeaderLine('Origin');

        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            $response = new Response(204);
            return $this->withCors($response, $origin);
        }

        $response = $handler->handle($request);

        return $this->withCors($response, $origin);
    }

    private function withCors(ResponseInterface $response, string $origin): ResponseInterface
    {
        if ($origin !== '' && (in_array($origin, $this->allowedOrigins, true) || in_array('*', $this->allowedOrigins, true))) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Vary', 'Origin');
        }

        return $response
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Admin-Token')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Max-Age', '86400');
    }
}
