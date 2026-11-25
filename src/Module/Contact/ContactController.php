<?php

declare(strict_types=1);

namespace App\Module\Contact;

use Doctrine\DBAL\Connection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ContactController
{
    private const ALLOWED_INTERESTS = [
        'POS System',
        'Online Ordering',
        'Analytics',
        'Full Suite',
    ];

    private const ALLOWED_STATUSES = [
        'new',
        'reviewed',
        'resolved',
        'archived',
    ];

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function store(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        try {
            $data = $request->getParsedBody();

            if (!is_array($data)) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Invalid request body.',
                ], 400);
            }

            $fullName = trim((string) ($data['fullName'] ?? ''));
            $email = strtolower(trim((string) ($data['email'] ?? '')));
            $phone = trim((string) ($data['phone'] ?? ''));
            $countryCode = trim((string) ($data['countryCode'] ?? '+92'));
            $interest = trim((string) ($data['interest'] ?? ''));
            $message = trim((string) ($data['message'] ?? ''));

            if (
                $fullName === '' ||
                $email === '' ||
                $phone === '' ||
                $interest === ''
            ) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Please complete all required fields.',
                ], 422);
            }

            if (
                mb_strlen($fullName) > 100 ||
                mb_strlen($email) > 254 ||
                mb_strlen($phone) > 25 ||
                mb_strlen($interest) > 100 ||
                mb_strlen($message) > 1000
            ) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'One or more fields exceed the allowed length.',
                ], 422);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Please provide a valid email address.',
                ], 422);
            }

            if (!preg_match('/^\+[0-9]{1,4}$/', $countryCode)) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Invalid country calling code.',
                ], 422);
            }

            $normalizedPhone = preg_replace('/[\s().-]+/', '', $phone);

            if (
                !is_string($normalizedPhone) ||
                !preg_match('/^[0-9]{5,20}$/', $normalizedPhone)
            ) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Please provide a valid phone number.',
                ], 422);
            }

            if (!in_array($interest, self::ALLOWED_INTERESTS, true)) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Invalid interest option.',
                ], 422);
            }

            $this->connection->insert('contact_submissions', [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $countryCode . ' ' . $normalizedPhone,
                'interest' => $interest,
                'message' => $message !== '' ? $message : null,
                'status' => 'new',
            ]);

            return $this->json($response, [
                'success' => true,
                'message' => 'Your message has been sent successfully.',
            ], 201);
        } catch (\Throwable $exception) {
            error_log(sprintf(
                '[ContactController::store] %s in %s:%d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ));

            return $this->json($response, [
                'success' => false,
                'message' => 'Unable to send your message. Please try again.',
            ], 500);
        }
    }

    public function adminIndex(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        try {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT id, full_name, email, phone, interest, message,
                        status, created_at, updated_at,
                        is_favorite, admin_note, is_deleted
                 FROM contact_submissions
                 ORDER BY created_at DESC
                 LIMIT 500'
            );

            return $this->json($response, [
                'success' => true,
                'submissions' => $rows,
            ]);
        } catch (\Throwable $exception) {
            error_log(sprintf(
                '[ContactController::adminIndex] %s in %s:%d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ));

            return $this->json($response, [
                'success' => false,
                'message' => 'Unable to load contact messages.',
            ], 500);
        }
    }

    public function adminUpdate(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args = []
    ): ResponseInterface {
        try {
            $id = $this->resolveId($request, $args);

            if ($id === null) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Invalid message ID.',
                ], 400);
            }

            $data = $request->getParsedBody();

            if (!is_array($data)) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Invalid request body.',
                ], 400);
            }

            $changes = [];

            if (array_key_exists('status', $data)) {
                $status = trim((string) $data['status']);

                if (!in_array($status, self::ALLOWED_STATUSES, true)) {
                    return $this->json($response, [
                        'success' => false,
                        'message' => 'Invalid message status.',
                    ], 422);
                }

                $changes['status'] = $status;
            }

            if (array_key_exists('is_favorite', $data)) {
                $favorite = filter_var(
                    $data['is_favorite'],
                    FILTER_VALIDATE_BOOLEAN,
                    FILTER_NULL_ON_FAILURE
                );

                if ($favorite === null) {
                    return $this->json($response, [
                        'success' => false,
                        'message' => 'Invalid favorite value.',
                    ], 422);
                }

                $changes['is_favorite'] = $favorite ? 1 : 0;
            }

            if (array_key_exists('admin_note', $data)) {
                if (!is_string($data['admin_note'])) {
                    return $this->json($response, [
                        'success' => false,
                        'message' => 'Invalid admin note.',
                    ], 422);
                }

                $note = trim($data['admin_note']);

                if (mb_strlen($note) > 5000) {
                    return $this->json($response, [
                        'success' => false,
                        'message' => 'Admin note cannot exceed 5000 characters.',
                    ], 422);
                }

                $changes['admin_note'] = $note !== '' ? $note : null;
            }

            if ($changes === []) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'No changes supplied.',
                ], 422);
            }

            $existing = $this->connection->fetchAssociative(
                'SELECT id FROM contact_submissions WHERE id = ?',
                [$id]
            );

            if (!$existing) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Message not found.',
                ], 404);
            }

            $this->connection->update('contact_submissions', $changes, ['id' => $id]);

            $updated = $this->connection->fetchAssociative(
                'SELECT id, full_name, email, phone, interest, message,
                        status, created_at, updated_at,
                        is_favorite, admin_note, is_deleted
                 FROM contact_submissions
                 WHERE id = ?',
                [$id]
            );

            return $this->json($response, [
                'success' => true,
                'message' => 'Message updated successfully.',
                'submission' => $updated,
            ]);
        } catch (\Throwable $exception) {
            error_log(sprintf(
                '[ContactController::adminUpdate] %s in %s:%d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ));

            return $this->json($response, [
                'success' => false,
                'message' => 'Unable to update message.',
            ], 500);
        }
    }

    public function adminDelete(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args = []
    ): ResponseInterface {
        try {
            $id = $this->resolveId($request, $args);

            if ($id === null) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Invalid message ID.',
                ], 400);
            }

            $existing = $this->connection->fetchAssociative(
                'SELECT id, is_deleted
                 FROM contact_submissions
                 WHERE id = ?',
                [$id]
            );

            if (!$existing) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Message not found.',
                ], 404);
            }

            if ((int) ($existing['is_deleted'] ?? 0) === 1) {
                return $this->json($response, [
                    'success' => true,
                    'message' => 'Message is already in Deleted.',
                ]);
            }

            $this->connection->update(
                'contact_submissions',
                ['is_deleted' => 1],
                ['id' => $id]
            );

            return $this->json($response, [
                'success' => true,
                'message' => 'Message moved to Deleted.',
            ]);
        } catch (\Throwable $exception) {
            error_log(sprintf(
                '[ContactController::adminDelete] %s in %s:%d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ));

            return $this->json($response, [
                'success' => false,
                'message' => 'Unable to delete message.',
            ], 500);
        }
    }

    private function resolveId(
        ServerRequestInterface $request,
        array $args
    ): ?int {
        $rawId = $args['id'] ?? null;

        if ($rawId === null || $rawId === '') {
            $path = $request->getUri()->getPath();

            if (preg_match(
                '#/api/admin/contact-submissions/([0-9]+)/?$#',
                $path,
                $matches
            )) {
                $rawId = $matches[1];
            }
        }

        $id = filter_var(
            $rawId,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]]
        );

        return $id === false || $id === null ? null : (int) $id;
    }

    private function json(
        ResponseInterface $response,
        array $data,
        int $status = 200
    ): ResponseInterface {
        $json = json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );

        $response->getBody()->write($json !== false ? $json : '{}');

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($status);
    }
}
