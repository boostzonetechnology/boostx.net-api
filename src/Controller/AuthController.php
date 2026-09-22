<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Admin;
use App\Service\AuthService;
use App\Support\JsonResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;

final class AuthController
{
    public function __construct(private readonly AuthService $auth)
    {
    }

    public function login(Request $request, Response $response): Response
    {
        $body = (array) $request->getParsedBody();
        try {
            $result = $this->auth->login(
                (string) ($body['email'] ?? ''),
                (string) ($body['password'] ?? ''),
            );
            return JsonResponse::ok($response, $result);
        } catch (InvalidArgumentException $e) {
            return JsonResponse::error($response, $e->getMessage(), 401);
        }
    }

    public function logout(Request $request, Response $response): Response
    {
        $token = (string) $request->getAttribute('admin_token', '');
        $this->auth->logout($token);

        return JsonResponse::ok($response, ['ok' => true]);
    }

    public function me(Request $request, Response $response): Response
    {
        /** @var Admin $admin */
        $admin = $request->getAttribute('admin');

        return JsonResponse::ok($response, ['admin' => $admin->toArray()]);
    }
}
