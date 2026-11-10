<?php

declare(strict_types=1);

namespace App\Module\RequestJoin;

use Doctrine\DBAL\Connection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestJoinController
{
    private const ALLOWED_MEMBERSHIPS = [
        'Business Member',
        'Individual Member',
        'B2B Reseller',
        'Technology Partner',
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
            $company = trim((string) ($data['company'] ?? ''));
            $country = trim((string) ($data['country'] ?? ''));
            $membership = trim((string) ($data['membership'] ?? ''));
            $message = trim((string) ($data['message'] ?? ''));

            if (
                $fullName === '' ||
                $email === '' ||
                $phone === '' ||
                $country === '' ||
                $membership === ''
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
                mb_strlen($company) > 150 ||
                mb_strlen($country) > 100 ||
                mb_strlen($membership) > 100 ||
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

            if (!in_array($membership, self::ALLOWED_MEMBERSHIPS, true)) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Invalid membership type.',
                ], 422);
            }

            $this->connection->insert('join_submissions', [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $countryCode . ' ' . $normalizedPhone,
                'company' => $company !== '' ? $company : null,
                'country' => $country,
                'membership_type' => $membership,
                'message' => $message !== '' ? $message : null,
                'status' => 'new',
            ]);

            return $this->json($response, [
                'success' => true,
                'message' => 'Your application has been submitted successfully.',
            ], 201);
        } catch (\Throwable $exception) {
            error_log(sprintf(
                '[RequestJoinController::store] %s in %s:%d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ));

            return $this->json($response, [
                'success' => false,
                'message' => 'Unable to save your application. Please check the backend error log.',
            ], 500);
        }
    }

    public function adminIndex(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        try {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT id, full_name, email, phone, company, country,
                        membership_type, message, status, created_at, updated_at,
                        is_favorite, admin_note, is_deleted
                 FROM join_submissions
                 ORDER BY created_at DESC
                 LIMIT 500'
            );

            return $this->json($response, [
                'success' => true,
                'submissions' => $rows,
            ]);
        } catch (\Throwable $exception) {
            error_log(sprintf(
                '[RequestJoinController::adminIndex] %s in %s:%d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ));

            return $this->json($response, [
                'success' => false,
                'message' => 'Unable to load submissions.',
            ], 500);
        }
    }

    public function adminUpdate(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args = []
    ): ResponseInterface {
        try {
            $id = $this->getApplicationId($args);$rawId = $args['id'] ?? null;

            if ($rawId === null || $rawId === '') {
            $path = $request->getUri()->getPath();

            if (preg_match(
            '#/api/admin/join-submissions/([0-9]+)/?$#',
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

           if ($id === false || $id === null) {
               return $this->json($response, [
                'success' => false,
                 'message' => 'Invalid application ID.',
                 ], 400);
               }

$id = (int) $id;

            if ($id === null) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Invalid application ID.',
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

                if (!in_array($status, ['new', 'reviewed', 'approved', 'rejected'], true)) {
                    return $this->json($response, [
                        'success' => false,
                        'message' => 'Invalid application status.',
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
                'SELECT id FROM join_submissions WHERE id = ?',
                [$id]
            );

            if (!$existing) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Application not found.',
                ], 404);
            }

            $this->connection->update('join_submissions', $changes, ['id' => $id]);

            $updated = $this->connection->fetchAssociative(
                'SELECT id, full_name, email, phone, company, country,
                        membership_type, message, status, created_at, updated_at,
                        is_favorite, admin_note, is_deleted
                 FROM join_submissions
                 WHERE id = ?',
                [$id]
            );

            return $this->json($response, [
                'success' => true,
                'message' => 'Application updated successfully.',
                'submission' => $updated,
            ]);
        } catch (\Throwable $exception) {
            error_log(sprintf(
                '[RequestJoinController::adminUpdate] %s in %s:%d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            ));

            return $this->json($response, [
                'success' => false,
                'message' => 'Unable to update application.',
            ], 500);
        }
    }

    
public function adminDelete(
    ServerRequestInterface $request,
    ResponseInterface $response,
    array $args = []
): ResponseInterface {
    try {
        // Read the ID from the route arguments first.
        // Fall back to the request path if route arguments are missing.
        $rawId = $args['id'] ?? null;

        if ($rawId === null || $rawId === '') {
            $path = $request->getUri()->getPath();

            if (preg_match(
                '#/api/admin/join-submissions/([0-9]+)/?$#',
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

        if ($id === false || $id === null) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Invalid application ID.',
            ], 400);
        }

        $existing = $this->connection->fetchAssociative(
            'SELECT id, is_deleted
             FROM join_submissions
             WHERE id = ?',
            [(int) $id]
        );

        if (!$existing) {
            return $this->json($response, [
                'success' => false,
                'message' => 'Application not found.',
            ], 404);
        }

        if ((int) ($existing['is_deleted'] ?? 0) === 1) {
            return $this->json($response, [
                'success' => true,
                'message' => 'Application is already in Deleted.',
            ]);
        }

        $this->connection->update(
            'join_submissions',
            ['is_deleted' => 1],
            ['id' => (int) $id]
        );

        return $this->json($response, [
            'success' => true,
            'message' => 'Application moved to Deleted.',
        ]);
    } catch (\Throwable $exception) {
        error_log(sprintf(
            '[RequestJoinController::adminDelete] %s in %s:%d',
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        ));

        return $this->json($response, [
            'success' => false,
            'message' => 'Unable to delete application.',
        ], 500);
    }
}


    private function getApplicationId(array $args): ?int
    {
        $rawId = $args['id'] ?? null;
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
