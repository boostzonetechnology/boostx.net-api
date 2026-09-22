<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Blog;
use App\Entity\BlogCategory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

/** @extends EntityRepository<Blog> */
class BlogRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em, ?ClassMetadata $class = null)
    {
        parent::__construct($em, $class ?? $em->getClassMetadata(Blog::class));
    }

    /** @return list<Blog> */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.category', 'c')->addSelect('c')
            ->where('b.published = true')
            ->orderBy('b.postAt', 'DESC')
            ->addOrderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /** @return list<Blog> */
    public function findAllAdmin(): array
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.category', 'c')->addSelect('c')
            ->orderBy('b.postAt', 'DESC')
            ->addOrderBy('b.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findPublishedBySlug(string $slug): ?Blog
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.category', 'c')->addSelect('c')
            ->where('b.slug = :slug')
            ->andWhere('b.published = true')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findAdminById(int $id): ?Blog
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.category', 'c')->addSelect('c')
            ->where('b.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function countByCategory(BlogCategory $category): int
    {
        return (int) $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $qb = $this->createQueryBuilder('b')
            ->select('COUNT(b.id)')
            ->where('b.slug = :slug')
            ->setParameter('slug', $slug);

        if ($ignoreId !== null) {
            $qb->andWhere('b.id != :id')->setParameter('id', $ignoreId);
        }

        return (int) $qb->getQuery()->getSingleScalarResult() > 0;
    }

    public function save(Blog $blog, bool $flush = true): void
    {
        $this->getEntityManager()->persist($blog);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Blog $blog, bool $flush = true): void
    {
        $this->getEntityManager()->remove($blog);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
