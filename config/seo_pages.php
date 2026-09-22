<?php

declare(strict_types=1);

/**
 * Seed source for `php bin/console app:seed-seo`.
 * Runtime SEO pages live in MySQL (`seo_pages`) and are managed in Super Admin → SEO.
 *
 * @return list<array{path: string, title: string, description: string, section: string}>
 */
return [
    [
        'path' => '/',
        'title' => 'Home',
        'description' => 'boostX restaurant operating system — POS, ordering, and ops in one platform.',
        'section' => 'Company',
    ],
    [
        'path' => '/about',
        'title' => 'About',
        'description' => 'Mission, vision, and why boostX exists for restaurants.',
        'section' => 'Company',
    ],
    [
        'path' => '/pricing',
        'title' => 'Pricing',
        'description' => 'Plans and pricing for boostX — 0% commission.',
        'section' => 'Company',
    ],
    [
        'path' => '/testimonials',
        'title' => 'Testimonials',
        'description' => 'What restaurants say about boostX.',
        'section' => 'Company',
    ],
    [
        'path' => '/faqs',
        'title' => 'FAQs',
        'description' => 'Frequently asked questions about boostX.',
        'section' => 'Company',
    ],
    [
        'path' => '/contact',
        'title' => 'Contact',
        'description' => 'Get in touch with the boostX team.',
        'section' => 'Company',
    ],
    [
        'path' => '/blog',
        'title' => 'Blog',
        'description' => 'Product news, restaurant ops, and platform stories.',
        'section' => 'Content',
    ],
    [
        'path' => '/point-of-sale',
        'title' => 'Point of Sale',
        'description' => 'Restaurant POS built for speed and accuracy.',
        'section' => 'Products',
    ],
    [
        'path' => '/online-ordering',
        'title' => 'Online Ordering',
        'description' => 'Commission-free online ordering for restaurants.',
        'section' => 'Products',
    ],
    [
        'path' => '/admin-panel',
        'title' => 'Admin Panel',
        'description' => 'Central control for menus, outlets, and operations.',
        'section' => 'Products',
    ],
    [
        'path' => '/warehouse-inventory',
        'title' => 'Warehouse & Inventory',
        'description' => 'Stock, warehouse, and inventory management.',
        'section' => 'Products',
    ],
    [
        'path' => '/kitchen-display',
        'title' => 'Kitchen Display',
        'description' => 'Kitchen display system for smoother ticket flow.',
        'section' => 'Products',
    ],
    [
        'path' => '/rider-management',
        'title' => 'Rider Management',
        'description' => 'Dispatch and rider app for deliveries.',
        'section' => 'Products',
    ],
    [
        'path' => '/analytics-reports',
        'title' => 'Analytics & Reports',
        'description' => 'Sales and operations analytics for restaurants.',
        'section' => 'Products',
    ],
    [
        'path' => '/expense-management',
        'title' => 'Expense Management',
        'description' => 'Track and control restaurant expenses.',
        'section' => 'Products',
    ],
    [
        'path' => '/hr-accounts',
        'title' => 'HR & Accounts',
        'description' => 'HR and accounting tools for restaurant teams.',
        'section' => 'Products',
    ],
];
