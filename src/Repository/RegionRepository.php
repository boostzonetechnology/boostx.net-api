<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Region;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

/** @extends EntityRepository<Region> */
class RegionRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em, ?ClassMetadata $class = null)
    {
        parent::__construct($em, $class ?? $em->getClassMetadata(Region::class));
    }

    /** @return list<Region> */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.sortOrder', 'ASC')
            ->addOrderBy('r.countryName', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByCountryCode(string $code): ?Region
    {
        return $this->findOneBy(['countryCode' => strtoupper(trim($code))]);
    }

    public function findEnabledByCountryCode(string $code): ?Region
    {
        return $this->createQueryBuilder('r')
            ->where('r.countryCode = :code')
            ->andWhere('r.isEnabled = true')
            ->setParameter('code', strtoupper(trim($code)))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findFirstEnabled(): ?Region
    {
        return $this->createQueryBuilder('r')
            ->where('r.isEnabled = true')
            ->orderBy('r.sortOrder', 'ASC')
            ->addOrderBy('r.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Region $region, bool $flush = true): void
    {
        $this->getEntityManager()->persist($region);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Region $region, bool $flush = true): void
    {
        $this->getEntityManager()->remove($region);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
