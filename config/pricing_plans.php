<?php

declare(strict_types=1);

/**
 * Default public pricing cards (seeded for DEFAULT_REGION_CODE / PK).
 *
 * @return list<array{
 *   name: string,
 *   slug: string,
 *   price: float|int|string,
 *   display_price: string,
 *   period_label: string,
 *   description: string,
 *   features: list<string>,
 *   is_highlighted: bool,
 *   billing_period: string,
 *   sort_order: int
 * }>
 */
return [
    [
        'name' => 'Starter',
        'slug' => 'starter',
        'price' => 0,
        'display_price' => 'Free',
        'period_label' => '',
        'description' => 'Perfect for small kiosks and food stalls.',
        'features' => ['1 POS Terminal', 'QR Menu', 'Basic Analytics', 'Email Support'],
        'is_highlighted' => false,
        'billing_period' => 'monthly',
        'sort_order' => 0,
    ],
    [
        'name' => 'Professional',
        'slug' => 'professional',
        'price' => 49,
        'display_price' => '$49',
        'period_label' => '/mo',
        'description' => 'Most popular for growing restaurants.',
        'features' => [
            'Unlimited POS Terminals',
            'Online Ordering',
            'KDS & Rider App',
            'Live Inventory',
            '24/7 Priority Support',
        ],
        'is_highlighted' => true,
        'billing_period' => 'monthly',
        'sort_order' => 1,
    ],
    [
        'name' => 'Enterprise',
        'slug' => 'enterprise',
        'price' => 0,
        'display_price' => 'Custom',
        'period_label' => '',
        'description' => 'Designed for multi-branch chains.',
        'features' => [
            'All Pro Features',
            'Multi-Warehouse',
            'Dedicated Account Manager',
            'API Access',
            'Custom Integrations',
        ],
        'is_highlighted' => false,
        'billing_period' => 'monthly',
        'sort_order' => 2,
    ],
];
