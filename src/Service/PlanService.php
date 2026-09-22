<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Plan;
use App\Repository\PlanRepository;
use App\Repository\RegionRepository;
use App\Support\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use RuntimeException;

final class PlanService
{
    public function __construct(
        private readonly PlanRepository $plans,
        private readonly RegionRepository $regions,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** @return list<array<string, mixed>> */
    public function list(): array
    {
        return array_map(
            static fn (Plan $p) => $p->toArray(),
            $this->plans->findAllOrdered()
        );
    }

    /** @return array<string, mixed> */
    public function get(int $id): array
    {
        $plan = $this->plans->find($id);
        if (!$plan) {
            throw new RuntimeException('Plan not found.', 404);
        }

        return $plan->toArray();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): array
    {
        $regionId = (int) ($data['region_id'] ?? 0);
        $region = $this->regions->find($regionId);
        if (!$region) {
            throw new InvalidArgumentException('Valid region_id is required.');
        }

        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            throw new InvalidArgumentException('Name is required.');
        }

        $slugInput = trim((string) ($data['slug'] ?? ''));
        $slug = $this->uniqueSlug($region, $slugInput !== '' ? Slugger::slugify($slugInput) : Slugger::slugify($name));
        $price = $this->normalizePrice($data['price'] ?? null);
        $billing = $this->normalizeBilling((string) ($data['billing_period'] ?? 'monthly'));
        $features = $this->normalizeFeatures($data['features'] ?? null);

        $plan = new Plan(
            $region,
            $name,
            $slug,
            $price,
            $billing,
            $features,
            (int) ($data['sort_order'] ?? 0),
            array_key_exists('is_enabled', $data) ? (bool) $data['is_enabled'] : true,
            $this->normalizeNullableString($data['description'] ?? null),
            array_key_exists('is_highlighted', $data) ? (bool) $data['is_highlighted'] : false,
            $this->normalizeNullableString($data['display_price'] ?? null),
            $this->normalizeNullableString($data['period_label'] ?? null),
        );
        $this->plans->save($plan);

        return $plan->toArray();
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data): array
    {
        $plan = $this->plans->find($id);
        if (!$plan) {
            throw new RuntimeException('Plan not found.', 404);
        }

        if (array_key_exists('region_id', $data)) {
            $region = $this->regions->find((int) $data['region_id']);
            if (!$region) {
                throw new InvalidArgumentException('Valid region_id is required.');
            }
            $plan->setRegion($region);
        }

        if (array_key_exists('name', $data)) {
            $name = trim((string) $data['name']);
            if ($name === '') {
                throw new InvalidArgumentException('Name is required.');
            }
            $plan->setName($name);
        }

        if (array_key_exists('slug', $data) || array_key_exists('name', $data) || array_key_exists('region_id', $data)) {
            $slugInput = trim((string) ($data['slug'] ?? ''));
            $base = $slugInput !== '' ? Slugger::slugify($slugInput) : Slugger::slugify($plan->getName());
            $plan->setSlug($this->uniqueSlug($plan->getRegion(), $base, $plan->getId()));
        }

        if (array_key_exists('price', $data)) {
            $plan->setPrice($this->normalizePrice($data['price']));
        }

        if (array_key_exists('billing_period', $data)) {
            $plan->setBillingPeriod($this->normalizeBilling((string) $data['billing_period']));
        }

        if (array_key_exists('features', $data)) {
            $plan->setFeatures($this->normalizeFeatures($data['features']));
        }

        if (array_key_exists('description', $data)) {
            $plan->setDescription($this->normalizeNullableString($data['description']));
        }

        if (array_key_exists('is_highlighted', $data)) {
            $plan->setIsHighlighted((bool) $data['is_highlighted']);
        }

        if (array_key_exists('display_price', $data)) {
            $plan->setDisplayPrice($this->normalizeNullableString($data['display_price']));
        }

        if (array_key_exists('period_label', $data)) {
            $plan->setPeriodLabel($this->normalizeNullableString($data['period_label']));
        }

        if (array_key_exists('is_enabled', $data)) {
            $plan->setIsEnabled((bool) $data['is_enabled']);
        }

        if (array_key_exists('sort_order', $data)) {
            $plan->setSortOrder((int) $data['sort_order']);
        }

        $this->em->flush();

        return $plan->toArray();
    }

    public function delete(int $id): void
    {
        $plan = $this->plans->find($id);
        if (!$plan) {
            throw new RuntimeException('Plan not found.', 404);
        }
        $this->plans->remove($plan);
    }

    private function normalizePrice(mixed $price): string
    {
        if ($price === null || $price === '') {
            throw new InvalidArgumentException('Price is required.');
        }
        if (!is_numeric($price) || (float) $price < 0) {
            throw new InvalidArgumentException('Price must be a valid non-negative number.');
        }

        return number_format((float) $price, 2, '.', '');
    }

    private function normalizeBilling(string $period): string
    {
        $period = strtolower(trim($period));
        $allowed = ['monthly', 'yearly', 'one_time'];
        if (!in_array($period, $allowed, true)) {
            throw new InvalidArgumentException('Billing period must be monthly, yearly, or one_time.');
        }

        return $period;
    }

    private function normalizeFeatures(mixed $features): ?string
    {
        if ($features === null) {
            return null;
        }
        if (is_array($features)) {
            $lines = array_values(array_filter(array_map(
                static fn ($line) => trim((string) $line),
                $features
            )));

            return $lines === [] ? null : implode("\n", $lines);
        }

        $text = trim((string) $features);

        return $text === '' ? null : $text;
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }

    private function uniqueSlug(\App\Entity\Region $region, string $base, ?int $ignoreId = null): string
    {
        $slug = $base !== '' ? $base : 'plan';
        $n = 2;
        while ($this->plans->slugExistsForRegion($region, $slug, $ignoreId)) {
            $slug = $base . '-' . $n;
            $n++;
        }

        return $slug;
    }
}
