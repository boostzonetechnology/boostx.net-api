<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Service\AuthService;
use App\Support\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Slim\Psr7\Response;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $this->auth->resolveBearer(
            $request->getHeaderLine('Authorization') ?: null,
            $request->getHeaderLine('X-Admin-Token') ?: null,
        );

        try {
            $admin = $this->auth->authenticate($token);
        } catch (RuntimeException $e) {
            $code = $e->getCode() >= 400 ? (int) $e->getCode() : 401;
            return JsonResponse::error(new Response(), $e->getMessage(), $code);
        }

        return $handler->handle(
            $request
                ->withAttribute('admin', $admin)
                ->withAttribute('admin_token', $token)
        );
    }
}
