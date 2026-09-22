<?php

declare(strict_types=1);

namespace App\Service;

use Oc\IpRegionDetector\Exceptions\GeoLocationException;
use Oc\IpRegionDetector\Exceptions\InvalidIpException;
use Oc\IpRegionDetector\GeoLocationService;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

final class GeoIpService
{
    public function __construct(
        private readonly GeoLocationService $geo,
    ) {
    }

    /**
     * @return array{country_code: ?string, used_fallback: bool}
     */
    public function detectCountry(ServerRequestInterface $request, ?string $overrideCountry = null): array
    {
        if ($overrideCountry !== null && trim($overrideCountry) !== '') {
            return [
                'country_code' => strtoupper(trim($overrideCountry)),
                'used_fallback' => false,
            ];
        }

        try {
            $location = $this->geo->locate($request);
            $code = $location->countryCode !== null ? strtoupper(trim($location->countryCode)) : null;

            return [
                'country_code' => $code !== '' ? $code : null,
                'used_fallback' => $code === null || $code === '',
            ];
        } catch (InvalidIpException|GeoLocationException|Throwable) {
            return [
                'country_code' => null,
                'used_fallback' => true,
            ];
        }
    }
}
