<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

/** @extends EntityRepository<Admin> */
class AdminRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em, ?ClassMetadata $class = null)
    {
        parent::__construct($em, $class ?? $em->getClassMetadata(Admin::class));
    }

    public function findByEmail(string $email): ?Admin
    {
        return $this->findOneBy(['email' => strtolower(trim($email))]);
    }

    public function save(Admin $admin, bool $flush = true): void
    {
        $this->getEntityManager()->persist($admin);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
