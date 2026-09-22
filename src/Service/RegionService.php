<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Region;
use App\Repository\PlanRepository;
use App\Repository\RegionRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use RuntimeException;

final class RegionService
{
    public function __construct(
        private readonly RegionRepository $regions,
        private readonly PlanRepository $plans,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return array_map(
            static fn (Region $r) => $r->toArray(),
            $this->regions->findAllOrdered()
        );
    }

    /** @return array<string, mixed> */
    public function get(int $id): array
    {
        $region = $this->regions->find($id);
        if (!$region) {
            throw new RuntimeException('Region not found.', 404);
        }

        return $region->toArray();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): array
    {
        [$name, $code, $currency, $icon] = $this->validatedFields($data);

        if ($this->regions->findByCountryCode($code)) {
            throw new InvalidArgumentException('Country code already exists.');
        }

        $region = new Region(
            $name,
            $code,
            $currency,
            $icon,
            (int) ($data['sort_order'] ?? 0),
            array_key_exists('is_enabled', $data) ? (bool) $data['is_enabled'] : true,
        );
        $this->regions->save($region);

        return $region->toArray();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): array
    {
        $region = $this->regions->find($id);
        if (!$region) {
            throw new RuntimeException('Region not found.', 404);
        }

        if (array_key_exists('country_name', $data)
            || array_key_exists('country_code', $data)
            || array_key_exists('currency_code', $data)
            || array_key_exists('currency_icon', $data)
        ) {
            $merged = [
                'country_name' => $data['country_name'] ?? $region->getCountryName(),
                'country_code' => $data['country_code'] ?? $region->getCountryCode(),
                'currency_code' => $data['currency_code'] ?? $region->getCurrencyCode(),
                'currency_icon' => $data['currency_icon'] ?? $region->getCurrencyIcon(),
            ];
            [$name, $code, $currency, $icon] = $this->validatedFields($merged);

            $existing = $this->regions->findByCountryCode($code);
            if ($existing && $existing->getId() !== $region->getId()) {
                throw new InvalidArgumentException('Country code already exists.');
            }

            $region->setCountryName($name);
            $region->setCountryCode($code);
            $region->setCurrencyCode($currency);
            $region->setCurrencyIcon($icon);
        }

        if (array_key_exists('is_enabled', $data)) {
            $region->setIsEnabled((bool) $data['is_enabled']);
        }
        if (array_key_exists('sort_order', $data)) {
            $region->setSortOrder((int) $data['sort_order']);
        }

        $this->em->flush();

        return $region->toArray();
    }

    public function delete(int $id): void
    {
        $region = $this->regions->find($id);
        if (!$region) {
            throw new RuntimeException('Region not found.', 404);
        }

        if ($this->plans->countByRegion($region) > 0) {
            throw new InvalidArgumentException('Cannot delete region while plans still use it.');
        }

        $this->regions->remove($region);
    }

    /**
     * @param array<string, mixed> $data
     * @return array{0: string, 1: string, 2: string, 3: string}
     */
    private function validatedFields(array $data): array
    {
        $name = trim((string) ($data['country_name'] ?? ''));
        $code = strtoupper(trim((string) ($data['country_code'] ?? '')));
        $currency = strtoupper(trim((string) ($data['currency_code'] ?? '')));
        $icon = trim((string) ($data['currency_icon'] ?? ''));

        if ($name === '' || $code === '' || $currency === '' || $icon === '') {
            throw new InvalidArgumentException('Country name, code, currency code, and currency icon are required.');
        }

        return [$name, $code, $currency, $icon];
    }
}
