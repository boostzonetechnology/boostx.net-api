<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\UploadService;
use App\Support\JsonResponse;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

final class UploadController
{
    public function __construct(private readonly UploadService $uploads)
    {
    }

    public function store(Request $request, Response $response): Response
    {
        $files = $request->getUploadedFiles();
        $uploaded = $files['file'] ?? null;
        if (!$uploaded instanceof UploadedFileInterface) {
            return JsonResponse::error($response, 'file is required.', 422);
        }

        try {
            return JsonResponse::ok($response, $this->uploads->store($uploaded), 201);
        } catch (InvalidArgumentException $e) {
            return JsonResponse::error($response, $e->getMessage(), 422);
        } catch (RuntimeException $e) {
            return JsonResponse::error($response, $e->getMessage(), 500);
        }
    }
}
