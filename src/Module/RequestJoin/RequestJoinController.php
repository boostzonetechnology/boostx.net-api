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

            // Required fields
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

            // Validate field lengths
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

            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Please provide a valid email address.',
                ], 422);
            }

            // Validate country calling code
            if (!preg_match('/^\+[0-9]{1,4}$/', $countryCode)) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Invalid country calling code.',
                ], 422);
            }

           // Validate phone number (country code is validated separately).
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

            // Validate membership selection
            if (!in_array(
                $membership,
                self::ALLOWED_MEMBERSHIPS,
                true
            )) {
                return $this->json($response, [
                    'success' => false,
                    'message' => 'Invalid membership type.',
                ], 422);
            }

            // Save submission to the database
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
                        membership_type, message, status, created_at, updated_at
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
