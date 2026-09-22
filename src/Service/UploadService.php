<?php

declare(strict_types=1);

namespace App\Service;

use App\Support\Env;
use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

final class UploadService
{
    public function __construct(
        private readonly string $uploadDir,
        private readonly string $uploadUrl,
        private readonly int $maxBytes,
    ) {
    }

    public function store(UploadedFileInterface $file): array
    {
        if ($file->getError() !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Upload failed.');
        }

        $size = $file->getSize() ?? 0;
        if ($size <= 0 || $size > $this->maxBytes) {
            throw new InvalidArgumentException('File too large.');
        }

        $stream = $file->getStream();
        $uri = $stream->getMetadata('uri');
        $mime = 'application/octet-stream';
        if (is_string($uri) && is_file($uri)) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($uri) ?: $mime;
        } else {
            $mime = $file->getClientMediaType() ?: $mime;
        }

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($allowed[$mime])) {
            throw new InvalidArgumentException('Only JPEG, PNG, WebP, or GIF images are allowed.');
        }

        if (!is_dir($this->uploadDir) && !mkdir($this->uploadDir, 0755, true) && !is_dir($this->uploadDir)) {
            throw new RuntimeException('Could not create upload directory.', 500);
        }

        $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
        $dest = $this->uploadDir . DIRECTORY_SEPARATOR . $name;
        $file->moveTo($dest);

        $path = rtrim($this->uploadUrl, '/') . '/' . $name;
        $base = rtrim((string) Env::get('APP_URL', ''), '/');
        $url = $base !== '' ? $base . $path : $path;

        return [
            'url' => $url,
            'path' => $path,
        ];
    }
}
