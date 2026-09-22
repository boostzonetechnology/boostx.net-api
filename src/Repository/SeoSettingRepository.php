<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SeoSetting;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;

/** @extends EntityRepository<SeoSetting> */
class SeoSettingRepository extends EntityRepository
{
    public function __construct(EntityManagerInterface $em, ?ClassMetadata $class = null)
    {
        parent::__construct($em, $class ?? $em->getClassMetadata(SeoSetting::class));
    }

    public function getValue(string $key, string $default = ''): string
    {
        $row = $this->find($key);
        if (!$row instanceof SeoSetting) {
            return $default;
        }

        return $row->getValue();
    }

    public function setValue(string $key, string $value): void
    {
        $row = $this->find($key);
        if ($row instanceof SeoSetting) {
            $row->setValue($value);
        } else {
            $this->getEntityManager()->persist(new SeoSetting($key, $value));
        }
        $this->getEntityManager()->flush();
    }

    /** @return array<string, string> */
    public function allAsMap(): array
    {
        $map = [];
        foreach ($this->findAll() as $row) {
            $map[$row->getKey()] = $row->getValue();
        }

        return $map;
    }
}
