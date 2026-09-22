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
use App\Middleware\CorsMiddleware;
use App\Repository\AdminRepository;
use App\Repository\AdminTokenRepository;
use App\Repository\BlogCategoryRepository;
use App\Repository\BlogRepository;
use App\Repository\PlanRepository;
use App\Repository\RegionRepository;
use App\Repository\SeoPageRepository;
use App\Repository\SeoSettingRepository;
use App\Service\AdminSeoService;
use App\Service\AuthService;
use App\Service\BlogService;
use App\Service\CategoryService;
use App\Service\GeoIpService;
use App\Service\PlanService;
use App\Service\PricingService;
use App\Service\RegionService;
use App\Service\SeoService;
use App\Service\UploadService;
use App\Support\DoctrineFactory;
use App\Support\Env;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use Nyholm\Psr7\Factory\Psr17Factory;
use Oc\IpRegionDetector\GeoLocationService;
use Oc\IpRegionDetector\IpResolver;
use Oc\IpRegionDetector\Providers\IpWhoisProvider;
use Psr\Container\ContainerInterface;

return [
    EntityManagerInterface::class => static fn (): EntityManagerInterface => DoctrineFactory::create(),

    AdminRepository::class => static fn (ContainerInterface $c): AdminRepository =>
        new AdminRepository($c->get(EntityManagerInterface::class)),

    AdminTokenRepository::class => static fn (ContainerInterface $c): AdminTokenRepository =>
        new AdminTokenRepository($c->get(EntityManagerInterface::class)),

    BlogCategoryRepository::class => static fn (ContainerInterface $c): BlogCategoryRepository =>
        new BlogCategoryRepository($c->get(EntityManagerInterface::class)),

    BlogRepository::class => static fn (ContainerInterface $c): BlogRepository =>
        new BlogRepository($c->get(EntityManagerInterface::class)),

    SeoPageRepository::class => static fn (ContainerInterface $c): SeoPageRepository =>
        new SeoPageRepository($c->get(EntityManagerInterface::class)),

    SeoSettingRepository::class => static fn (ContainerInterface $c): SeoSettingRepository =>
        new SeoSettingRepository($c->get(EntityManagerInterface::class)),

    RegionRepository::class => static fn (ContainerInterface $c): RegionRepository =>
        new RegionRepository($c->get(EntityManagerInterface::class)),

    PlanRepository::class => static fn (ContainerInterface $c): PlanRepository =>
        new PlanRepository($c->get(EntityManagerInterface::class)),

    AuthService::class => static fn (ContainerInterface $c): AuthService =>
        new AuthService(
            $c->get(AdminRepository::class),
            $c->get(AdminTokenRepository::class),
            Env::int('TOKEN_TTL_SECONDS', 604800),
        ),

    CategoryService::class => static fn (ContainerInterface $c): CategoryService =>
        new CategoryService(
            $c->get(BlogCategoryRepository::class),
            $c->get(BlogRepository::class),
            $c->get(EntityManagerInterface::class),
        ),

    BlogService::class => static fn (ContainerInterface $c): BlogService =>
        new BlogService(
            $c->get(BlogRepository::class),
            $c->get(BlogCategoryRepository::class),
            $c->get(EntityManagerInterface::class),
        ),

    UploadService::class => static fn (): UploadService =>
        new UploadService(
            dirname(__DIR__) . '/public/uploads/blog',
            '/uploads/blog',
            Env::int('UPLOAD_MAX_BYTES', 5 * 1024 * 1024),
        ),

    SeoService::class => static fn (ContainerInterface $c): SeoService =>
        new SeoService(
            $c->get(SeoPageRepository::class),
            $c->get(SeoSettingRepository::class),
            $c->get(BlogRepository::class),
        ),

    AdminSeoService::class => static fn (ContainerInterface $c): AdminSeoService =>
        new AdminSeoService(
            $c->get(SeoPageRepository::class),
            $c->get(SeoSettingRepository::class),
            $c->get(EntityManagerInterface::class),
        ),

    RegionService::class => static fn (ContainerInterface $c): RegionService =>
        new RegionService(
            $c->get(RegionRepository::class),
            $c->get(PlanRepository::class),
            $c->get(EntityManagerInterface::class),
        ),

    PlanService::class => static fn (ContainerInterface $c): PlanService =>
        new PlanService(
            $c->get(PlanRepository::class),
            $c->get(RegionRepository::class),
            $c->get(EntityManagerInterface::class),
        ),

    GeoLocationService::class => static function (): GeoLocationService {
        $psr17 = new Psr17Factory();
        $httpClient = new Client([
            'timeout' => 5,
            'connect_timeout' => 5,
        ]);

        return new GeoLocationService(
            ipResolver: new IpResolver(),
            provider: new IpWhoisProvider(
                httpClient: $httpClient,
                requestFactory: $psr17,
                baseUrl: 'https://ipwho.is',
            ),
        );
    },

    GeoIpService::class => static fn (ContainerInterface $c): GeoIpService =>
        new GeoIpService($c->get(GeoLocationService::class)),

    PricingService::class => static fn (ContainerInterface $c): PricingService =>
        new PricingService(
            $c->get(GeoIpService::class),
            $c->get(RegionRepository::class),
            $c->get(PlanRepository::class),
        ),

    AuthMiddleware::class => static fn (ContainerInterface $c): AuthMiddleware =>
        new AuthMiddleware($c->get(AuthService::class)),

    CorsMiddleware::class => static fn (): CorsMiddleware =>
        new CorsMiddleware(Env::list('CORS_ORIGINS', 'http://127.0.0.1:5173,http://localhost:5173')),

    AuthController::class => static fn (ContainerInterface $c): AuthController =>
        new AuthController($c->get(AuthService::class)),

    CategoryController::class => static fn (ContainerInterface $c): CategoryController =>
        new CategoryController($c->get(CategoryService::class)),

    BlogController::class => static fn (ContainerInterface $c): BlogController =>
        new BlogController($c->get(BlogService::class)),

    UploadController::class => static fn (ContainerInterface $c): UploadController =>
        new UploadController($c->get(UploadService::class)),

    SeoController::class => static fn (ContainerInterface $c): SeoController =>
        new SeoController($c->get(SeoService::class)),

    AdminSeoController::class => static fn (ContainerInterface $c): AdminSeoController =>
        new AdminSeoController($c->get(AdminSeoService::class)),

    RegionController::class => static fn (ContainerInterface $c): RegionController =>
        new RegionController($c->get(RegionService::class)),

    PlanController::class => static fn (ContainerInterface $c): PlanController =>
        new PlanController($c->get(PlanService::class)),

    PricingController::class => static fn (ContainerInterface $c): PricingController =>
        new PricingController($c->get(PricingService::class)),

    HealthController::class => static fn (): HealthController => new HealthController(),
];
