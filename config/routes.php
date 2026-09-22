<?php

declare(strict_types=1);

use App\Controller\AdminSeoController;
use App\Controller\AuthController;
use App\Controller\BlogController;
use App\Controller\CategoryController;
use App\Controller\HealthController;
use App\Controller\PlanController;
use App\Controller\PricingController;
use App\Controller\RegionController;
use App\Controller\SeoController;
use App\Controller\UploadController;
use App\Middleware\AuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return static function (App $app): void {
    $app->get('/robots.txt', [SeoController::class, 'robots']);
    $app->get('/sitemap.xml', [SeoController::class, 'sitemap']);
    $app->get('/llms.txt', [SeoController::class, 'llms']);

    $app->get('/api/health', [HealthController::class, 'index']);
    $app->get('/api/pricing', [PricingController::class, 'index']);

    $app->group('/api/auth', function (RouteCollectorProxy $group): void {
        $group->post('/login', [AuthController::class, 'login']);
        $group->post('/logout', [AuthController::class, 'logout'])->add(AuthMiddleware::class);
        $group->get('/me', [AuthController::class, 'me'])->add(AuthMiddleware::class);
    });

    $app->group('/api/blogs', function (RouteCollectorProxy $group): void {
        $group->get('', [BlogController::class, 'publicIndex']);
        $group->get('/{slug}', [BlogController::class, 'publicShow']);
    });

    $app->group('/api/admin', function (RouteCollectorProxy $group): void {
        $group->get('/blogs', [BlogController::class, 'adminIndex']);
        $group->get('/blogs/{id}', [BlogController::class, 'adminShow']);
        $group->post('/blogs', [BlogController::class, 'store']);
        $group->put('/blogs/{id}', [BlogController::class, 'update']);
        $group->delete('/blogs/{id}', [BlogController::class, 'destroy']);

        $group->get('/categories', [CategoryController::class, 'index']);
        $group->get('/categories/{id}', [CategoryController::class, 'show']);
        $group->post('/categories', [CategoryController::class, 'store']);
        $group->put('/categories/{id}', [CategoryController::class, 'update']);
        $group->delete('/categories/{id}', [CategoryController::class, 'destroy']);

        $group->get('/seo/pages', [AdminSeoController::class, 'pagesIndex']);
        $group->get('/seo/pages/{id}', [AdminSeoController::class, 'pagesShow']);
        $group->post('/seo/pages', [AdminSeoController::class, 'pagesStore']);
        $group->put('/seo/pages/{id}', [AdminSeoController::class, 'pagesUpdate']);
        $group->delete('/seo/pages/{id}', [AdminSeoController::class, 'pagesDestroy']);
        $group->get('/seo/settings', [AdminSeoController::class, 'settingsShow']);
        $group->put('/seo/settings', [AdminSeoController::class, 'settingsUpdate']);

        $group->get('/regions', [RegionController::class, 'index']);
        $group->get('/regions/{id}', [RegionController::class, 'show']);
        $group->post('/regions', [RegionController::class, 'store']);
        $group->put('/regions/{id}', [RegionController::class, 'update']);
        $group->delete('/regions/{id}', [RegionController::class, 'destroy']);

        $group->get('/plans', [PlanController::class, 'index']);
        $group->get('/plans/{id}', [PlanController::class, 'show']);
        $group->post('/plans', [PlanController::class, 'store']);
        $group->put('/plans/{id}', [PlanController::class, 'update']);
        $group->delete('/plans/{id}', [PlanController::class, 'destroy']);

        $group->post('/uploads', [UploadController::class, 'store']);
    })->add(AuthMiddleware::class);
};
