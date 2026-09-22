<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AdminTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdminTokenRepository::class)]
#[ORM\Table(name: 'admin_tokens')]
class AdminToken
{
    #[ORM\Id]
    #[ORM\Column(length: 64)]
    private string $token;

    #[ORM\ManyToOne(targetEntity: Admin::class)]
    #[ORM\JoinColumn(name: 'admin_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Admin $admin;

    #[ORM\Column(name: 'expires_at')]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    public function __construct(string $token, Admin $admin, DateTimeImmutable $expiresAt)
    {
        $this->token = $token;
        $this->admin = $admin;
        $this->expiresAt = $expiresAt;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getAdmin(): Admin
    {
        return $this->admin;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function isExpired(?DateTimeImmutable $now = null): bool
    {
        $now ??= new DateTimeImmutable();

        return $this->expiresAt <= $now;
    }
}
