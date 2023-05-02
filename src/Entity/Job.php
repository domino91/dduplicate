<?php

namespace App\Entity;

use App\Repository\JobRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobRepository::class)]
class Job
{
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in progress';
    const STATUS_DONE = 'done';
    const STATUS_ERROR = 'error';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'text')]
    private ?string $path = null;

    #[ORM\Column(type: 'text')]
    private ?string $status = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?string $errorLog = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt = null): void
    {
        $this->createdAt = $createdAt;
    }

    public function getErrorLog(): ?string
    {
        return $this->errorLog;
    }

    public function setErrorLog(?string $errorLog): void
    {
        $this->errorLog = $errorLog;
    }
}
