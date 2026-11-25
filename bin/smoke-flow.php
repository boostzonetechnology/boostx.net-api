<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Support\Env;
use App\Service\AuthService;
use App\Service\CategoryService;
use App\Service\BlogService;
use DI\ContainerBuilder;

Env::load(dirname(__DIR__));

$builder = new ContainerBuilder();
$builder->addDefinitions(require dirname(__DIR__) . '/config/dependencies.php');
$c = $builder->build();

/** @var AuthService $auth */
$auth = $c->get(AuthService::class);
$login = $auth->login(
    (string) Env::get('ADMIN_EMAIL'),
    (string) Env::get('ADMIN_PASSWORD'),
);
echo "login ok token=" . substr($login['token'], 0, 8) . "...\n";

/** @var CategoryService $categories */
$categories = $c->get(CategoryService::class);
$cat = $categories->create(['name' => 'News']);
echo "category ok id={$cat['id']}\n";

/** @var BlogService $blogs */
$blogs = $c->get(BlogService::class);
$post = $blogs->create([
    'title' => 'How BoostX connects POS, kitchen, and delivery',
    'category_id' => $cat['id'],
    'excerpt' => 'A practical walkthrough of the restaurant operating workflow — from order capture to kitchen tickets and rider handoff.',
    'content' => [
        'blocks' => [
            [
                'id' => 't1',
                'type' => 'text',
                'html' => '<p>BoostX brings ordering, kitchen display, and delivery into one connected flow so restaurant teams spend less time switching tools.</p><p>This walkthrough covers how a ticket moves from POS or QR ordering into the kitchen, then out to riders when delivery is enabled.</p>',
            ],
        ],
    ],
    'published' => true,
    'post_at' => date('Y-m-d H:i:s'),
]);
echo "blog ok id={$post['id']} slug={$post['slug']}\n";
echo "public count=" . count($blogs->listPublished()) . "\n";
echo "SMOKE PASS\n";
