<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SeoPage;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

/** @extends EntityRepository<SeoPage> */
class SeoPageRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em, ?ClassMetadata $class = null)
    {
        parent::__construct($em, $class ?? $em->getClassMetadata(SeoPage::class));
    }

    /** @return list<SeoPage> */
    public function findAllOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<SeoPage> */
    public function findEnabledOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isEnabled = true')
            ->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<SeoPage> */
    public function findDisabledOrdered(): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.isEnabled = false')
            ->orderBy('p.sortOrder', 'ASC')
            ->addOrderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByPath(string $path): ?SeoPage
    {
        return $this->findOneBy(['path' => SeoPage::normalizePath($path)]);
    }

    public function save(SeoPage $page, bool $flush = true): void
    {
        $this->getEntityManager()->persist($page);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SeoPage $page, bool $flush = true): void
    {
        $this->getEntityManager()->remove($page);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
