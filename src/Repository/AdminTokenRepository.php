<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdminToken;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

/** @extends EntityRepository<AdminToken> */
class AdminTokenRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em, ?ClassMetadata $class = null)
    {
        parent::__construct($em, $class ?? $em->getClassMetadata(AdminToken::class));
    }

    public function findValid(string $token): ?AdminToken
    {
        /** @var AdminToken|null $row */
        $row = $this->find($token);
        if (!$row || $row->isExpired()) {
            return null;
        }

        return $row;
    }

    public function save(AdminToken $token, bool $flush = true): void
    {
        $this->getEntityManager()->persist($token);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AdminToken $token, bool $flush = true): void
    {
        $this->getEntityManager()->remove($token);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function purgeExpired(?DateTimeImmutable $now = null): int
    {
        $now ??= new DateTimeImmutable();

        return $this->getEntityManager()->createQueryBuilder()
            ->delete(AdminToken::class, 't')
            ->where('t.expiresAt <= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->execute();
    }
}
