<?php

declare(strict_types=1);

namespace App\Module\RequestJoin;

use Doctrine\DBAL\Connection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class RequestJoinController
{
    /**
     * Restaurant types supported by the frontend form.
     */
    private const ALLOWED_RESTAURANT_TYPES = [
        'Fine Dining',
        'Casual Dining',
        'Fast Food',
        'Cafe',
        'Bakery',
        'Food Truck',
        'Cloud Kitchen',
        'Bar and Grill',
        'Other',
    ];

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * Submit a new restaurant application.
     *
     * Frontend endpoint:
     * POST /api/join-submissions
     */
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

            /*
             * Customer name:
             * Supports both the current frontend field "fullName"
             * and the alternative field "customerName".
             */
            $fullName = trim(
                (string) (
                    $data['customerName']
                    ?? $data['fullName']
                    ?? ''
                )
            );

            $email = strtolower(
                trim((string) ($data['email'] ?? ''))
            );

            $phone = trim(
                (string) ($data['phone'] ?? '')
            );

            $countryCode = trim(
                (string) ($data['countryCode'] ?? '+92')
            );

            $company = trim(
                (string) ($data['company'] ?? '')
            );

            $country = trim(
                (string) ($data['country'] ?? '')
            );

            /*
             * Restaurant type:
             * Supports the current "restaurantType" field.
             * The "membership" fallback preserves compatibility
             * with older frontend requests.
             */
            $restaurantType = trim(
                (string) (
                    $data['restaurantType']
                    ?? $data['membership']
                    ?? ''
                )
            );

            $message = trim(
                (string) ($data['message'] ?? '')
            );

            /*
             * Required field validation.
             */
            if (
                $fullName === ''
                || $email === ''
                || $phone === ''
                || $country === ''
                || $restaurantType === ''
            ) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Please complete all required fields.',
                ], 422);
            }

            /*
             * Length validation.
             */
            if (
                mb_strlen($fullName) > 100
                || mb_strlen($email) > 254
                || mb_strlen($phone) > 25
                || mb_strlen($countryCode) > 5
                || mb_strlen($company) > 150
                || mb_strlen($country) > 100
                || mb_strlen($restaurantType) > 100
                || mb_strlen($message) > 1000
            ) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'One or more fields exceed the allowed length.',
                ], 422);
            }

            /*
             * Validate email address.
             */
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Please provide a valid email address.',
                ], 422);
            }

            /*
             * Validate international calling code.
             */
            if (!preg_match('/^\+[0-9]{1,4}$/', $countryCode)) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Invalid country calling code.',
                ], 422);
            }

            /*
             * Normalize and validate phone number.
             */
            $normalizedPhone = preg_replace(
                '/[\s().-]+/',
                '',
                $phone
            );

            if (
                !is_string($normalizedPhone)
                || !preg_match('/^[0-9]{5,20}$/', $normalizedPhone)
            ) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Please provide a valid phone number.',
                ], 422);
            }

            /*
             * Validate the selected restaurant type against
             * the options displayed in the React frontend.
             */
            if (
                !in_array(
                    $restaurantType,
                    self::ALLOWED_RESTAURANT_TYPES,
                    true
                )
            ) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Invalid restaurant type.',
                ], 422);
            }

            /*
             * Save the application.
             *
             * IMPORTANT:
             * The existing database column "membership_type"
             * is retained for backward compatibility.
             *
             * No database column rename is required for this
             * controller change.
             */
            $this->connection->insert('join_submissions', [
                'full_name' => $fullName,
                'email' => $email,
                'phone' => $countryCode . ' ' . $normalizedPhone,
                'company' => $company !== '' ? $company : null,
                'country' => $country,

                // Store the selected restaurant type here.
                'membership_type' => $restaurantType,

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

    /**
     * Retrieve applications for the admin panel.
     *
     * Existing route:
     * GET /api/admin/join-submissions
     */
    public function adminIndex(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        try {
            $rows = $this->connection->fetchAllAssociative(
                'SELECT
                    id,
                    full_name,
                    email,
                    phone,
                    company,
                    country,
                    membership_type,
                    membership_type AS restaurant_type,
                    message,
                    status,
                    created_at,
                    updated_at,
                    is_favorite,
                    admin_note,
                    is_deleted
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

    /**
     * Update application status, favorite state, or admin note.
     *
     * Existing route:
     * PATCH /api/admin/join-submissions/{id}
     */
    public function adminUpdate(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args = []
    ): ResponseInterface {
        try {
            /*
             * Read the ID from route arguments.
             * Fall back to the URL path if necessary.
             */
            $rawId = $args['id'] ?? null;

            if ($rawId === null || $rawId === '') {
                $path = $request->getUri()->getPath();

                if (
                    preg_match(
                        '#/api/admin/join-submissions/([0-9]+)/?$#',
                        $path,
                        $matches
                    )
                ) {
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

            $data = $request->getParsedBody();

            if (!is_array($data)) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Invalid request body.',
                ], 400);
            }

            $changes = [];

            /*
             * Update application status.
             */
            if (array_key_exists('status', $data)) {
                $status = trim((string) $data['status']);

                if (
                    !in_array(
                        $status,
                        ['new', 'reviewed', 'approved', 'rejected'],
                        true
                    )
                ) {
                    return $this->json($response, [
                        'success' => false,
                        'message' => 'Invalid application status.',
                    ], 422);
                }

                $changes['status'] = $status;
            }

            /*
             * Update favorite state.
             */
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

            /*
             * Update internal admin note.
             */
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

                $changes['admin_note'] = $note !== ''
                    ? $note
                    : null;
            }

            if ($changes === []) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'No changes supplied.',
                ], 422);
            }

            /*
             * Confirm the application exists.
             */
            $existing = $this->connection->fetchAssociative(
                'SELECT id
                 FROM join_submissions
                 WHERE id = ?',
                [$id]
            );

            if (!$existing) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Application not found.',
                ], 404);
            }

            /*
             * Persist changes.
             */
            $this->connection->update(
                'join_submissions',
                $changes,
                ['id' => $id]
            );

            /*
             * Return the updated application.
             */
            $updated = $this->connection->fetchAssociative(
                'SELECT
                    id,
                    full_name,
                    email,
                    phone,
                    company,
                    country,
                    membership_type,
                    membership_type AS restaurant_type,
                    message,
                    status,
                    created_at,
                    updated_at,
                    is_favorite,
                    admin_note,
                    is_deleted
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

    /**
     * Soft-delete an application.
     *
     * Existing route:
     * DELETE /api/admin/join-submissions/{id}
     */
    public function adminDelete(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args = []
    ): ResponseInterface {
        try {
            /*
             * Read the ID from route arguments first.
             * Fall back to the URL path if necessary.
             */
            $rawId = $args['id'] ?? null;

            if ($rawId === null || $rawId === '') {
                $path = $request->getUri()->getPath();

                if (
                    preg_match(
                        '#/api/admin/join-submissions/([0-9]+)/?$#',
                        $path,
                        $matches
                    )
                ) {
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

            /*
             * Check whether the application exists.
             */
            $existing = $this->connection->fetchAssociative(
                'SELECT id, is_deleted
                 FROM join_submissions
                 WHERE id = ?',
                [$id]
            );

            if (!$existing) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Application not found.',
                ], 404);
            }

            /*
             * Avoid processing an already deleted application.
             */
            if ((int) ($existing['is_deleted'] ?? 0) === 1) {
                return $this->json($response, [
                    'success' => true,
                    'message' => 'Application is already in Deleted.',
                ]);
            }

            /*
             * Soft-delete rather than permanently deleting the row.
             */
            $this->connection->update(
                'join_submissions',
                ['is_deleted' => 1],
                ['id' => $id]
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

    /**
     * Return a JSON response.
     */
    private function json(
        ResponseInterface $response,
        array $data,
        int $status = 200
    ): ResponseInterface {
        $json = json_encode(
            $data,
            JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );

        $response->getBody()->write(
            $json !== false ? $json : '{}'
        );

        return $response
            ->withHeader(
                'Content-Type',
                'application/json; charset=utf-8'
            )
            ->withStatus($status);
    }
}