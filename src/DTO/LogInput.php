<?php

declare(strict_types=1);

namespace App\DTO;

use App\Entity\Log;
use DateTimeImmutable;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator\StatusCodeConstraint;
use App\Validator\ServiceNameConstraint;

class LogInput
{
    #[ServiceNameConstraint]
    #[SerializedName("serviceName")]
    public ?string $serviceName = null;

    #[Assert\NotNull]
    #[Assert\DateTime]
    #[SerializedName("logDate")]
    public ?DateTimeImmutable $logDate = null;


    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[SerializedName("action")]
    public ?string $action = null;


    #[StatusCodeConstraint]
    #[SerializedName("statusCode")]
    public ?int $statusCode = null;

    public static function createFromEntity(?Log $log): self
    {
        $dto = new LogInput();

        // not an edit, so just return an empty DTO
        if (!$log) {
            return $dto;
        }
        $dto->serviceName = $log->getServiceName();
        $dto->logDate     = $log->getLogDate();
        $dto->action      = $log->getAction();
        $dto->statusCode  = $log->getStatusCode();
        return $dto;
    }

    public function createOrUpdateEntity(?Log $log): Log
    {
        if (!$log) {
            $log = new Log();
        }
        $log->setServiceName($this->serviceName);
        $log->setLogDate($this->logDate);
        $log->setAction($this->action);
        $log->setStatusCode($this->statusCode);
        return $log;
    }

}
