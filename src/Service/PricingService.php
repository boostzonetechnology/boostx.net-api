<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Plan;
use App\Entity\Region;
use App\Repository\PlanRepository;
use App\Repository\RegionRepository;
use App\Support\Env;
use Psr\Http\Message\ServerRequestInterface;

final class PricingService
{
    public function __construct(
        private readonly GeoIpService $geoIp,
        private readonly RegionRepository $regions,
        private readonly PlanRepository $plans,
    ) {
    }

    /** @return array<string, mixed> */
    public function forRequest(ServerRequestInterface $request): array
    {
        $params = $request->getQueryParams();
        $override = isset($params['country']) ? (string) $params['country'] : null;
        $detected = $this->geoIp->detectCountry($request, $override);

        $countryCode = $detected['country_code'];
        $usedFallback = $detected['used_fallback'];
        $region = null;

        if ($countryCode !== null) {
            $region = $this->regions->findEnabledByCountryCode($countryCode);
        }

        if (!$region instanceof Region) {
            $defaultCode = strtoupper(trim((string) Env::get('DEFAULT_REGION_CODE', 'PK')));
            $region = $this->regions->findEnabledByCountryCode($defaultCode)
                ?? $this->regions->findFirstEnabled();
            $usedFallback = true;
        }

        $planRows = $region instanceof Region
            ? $this->plans->findEnabledByRegion($region)
            : [];

        return [
            'detected_country_code' => $detected['country_code'],
            'used_fallback' => $usedFallback,
            'region' => $region instanceof Region ? [
                'id' => $region->getId(),
                'country_name' => $region->getCountryName(),
                'country_code' => $region->getCountryCode(),
                'currency_code' => $region->getCurrencyCode(),
            ] : null,
            'plans' => array_map(
                static fn (Plan $plan): array => $plan->toPublicArray(),
                $planRows
            ),
        ];
    }
}
