<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\LogRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV4;

#[ORM\Entity(repositoryClass: LogRepository::class)]
class Log
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id;

    #[ORM\Column(type: 'uuid')]
    private Uuid|UuidV4 $uuid;

    #[ORM\Column(type: 'string', length: 150)]
    #[SerializedName("ServiceName")]
    private ?string $serviceName;

    #[ORM\Column(type: 'datetime_immutable')]
    #[SerializedName("LogDate")]
    private DateTimeImmutable $logDate;

    #[ORM\Column(type: 'string', length: 255)]
    #[SerializedName("action")]
    private ?string $action;

    #[ORM\Column(type: 'integer')]
    #[SerializedName("statusCode:")]
    private ?int $statusCode;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;


    public function __construct(string $uuid = null)
    {
        $this->createdAt = new DateTimeImmutable();
        $this->uuid      = ($uuid !== null) ? Uuid::fromString($uuid) : Uuid::v4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getServiceName(): ?string
    {
        return $this->serviceName;
    }

    public function setServiceName(string $serviceName): self
    {
        $this->serviceName = $serviceName;

        return $this;
    }

    public function getLogDate(): DateTimeImmutable
    {
        return $this->logDate;
    }

    public function setLogDate(DateTimeImmutable $logDate): self
    {
        $this->logDate = $logDate;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;

        return $this;
    }


    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUuid(): UuidV4|Uuid
    {
        return $this->uuid;
    }

    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }
}
