<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Plan;
use App\Entity\Region;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

/** @extends EntityRepository<Plan> */
class PlanRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em, ?ClassMetadata $class = null)
    {
        parent::__construct($em, $class ?? $em->getClassMetadata(Plan::class));
    }

    /** @return list<Plan> */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.region', 'r')->addSelect('r')
            ->orderBy('r.countryName', 'ASC')
            ->addOrderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByRegion(Region $region): int
    {
        return (int) $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.region = :region')
            ->setParameter('region', $region)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /** @return list<Plan> */
    public function findEnabledByRegion(Region $region): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.region = :region')
            ->andWhere('p.isEnabled = true')
            ->setParameter('region', $region)
            ->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByRegionAndSlug(Region $region, string $slug): ?Plan
    {
        return $this->findOneBy([
            'region' => $region,
            'slug' => $slug,
        ]);
    }

    public function slugExistsForRegion(Region $region, string $slug, ?int $ignoreId = null): bool
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.region = :region')
            ->andWhere('p.slug = :slug')
            ->setParameter('region', $region)
            ->setParameter('slug', $slug);

        if ($ignoreId !== null) {
            $qb->andWhere('p.id != :id')->setParameter('id', $ignoreId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function save(Plan $plan, bool $flush = true): void
    {
        $this->getEntityManager()->persist($plan);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Plan $plan, bool $flush = true): void
    {
        $this->getEntityManager()->remove($plan);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
