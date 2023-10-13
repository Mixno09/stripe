<?php

namespace App\Entity;

use App\Repository\CheckoutSessionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;

#[ORM\Entity(repositoryClass: CheckoutSessionRepository::class)]
class CheckoutSession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $sessionId = null;

    #[ORM\Column(length: 255)]
    private ?string $mode = null;
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subStatus = null;
    #[ManyToOne(targetEntity: User::class, cascade: ['remove'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSessionId(): ?string
    {
        return $this->sessionId;
    }

    public function setSessionId(string $sessionId): void
    {
        $this->sessionId = $sessionId;
    }

    public function getMode(): ?string
    {
        return $this->mode;
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getSubStatus(): ?string
    {
        return $this->subStatus;
    }

    public function setSubStatus(?string $subStatus): void
    {
        $this->subStatus = $subStatus;
    }
}
