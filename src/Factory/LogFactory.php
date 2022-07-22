<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Log;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;
use Zenstruck\Foundry\ModelFactory;

class LogFactory extends ModelFactory
{
    private const HTTP_METHODS       = ['GET', 'POST', 'PATCH', 'PUT', 'DELETE'];
    private const STATUS_CODE = [200, 201, 204, 400, 403, 422, 500];
    private const SERVICE_NAME       = ['USER-SERVICE', 'INVOICE-SERVICE'];

    protected static function getClass(): string
    {
        return Log::class;
    }

    protected function getDefaults(): array
    {
        return [
            'uuid'         => Uuid::v4(),
            'serviceName'  => self::SERVICE_NAME[self::faker()->numberBetween(0, 1)],
            'logDate'      => DateTimeImmutable::createFromMutable(self::faker()->dateTime()),
            'action'       => self::HTTP_METHODS[self::faker()->numberBetween(0, 4)] . self::faker()->slug(),
            'statusCode' => self::STATUS_CODE[self::faker()->numberBetween(0, 6)],
            'createdAt'    => new DateTimeImmutable()
        ];
    }

    protected function initialize(): self
    {
        return $this;
    }
}
