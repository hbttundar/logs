<?php

declare(strict_types=1);

namespace App\Test;

use App\Factory\LogFactory;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $this->client = static::createClient();
        self::bootKernel();
    }


    protected function getEntityManager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

    protected function generateFakeData(): void
    {
        LogFactory::createMany(
            3,
            [
                'serviceName' => 'USER-SERVICE',
                'statusCode'  => '200',
                'logDate'     => new DateTimeImmutable('2022-01-01 12:00:00')
            ]
        );
        LogFactory::createMany(
            3,
            [
                'serviceName' => 'INVOICE-SERVICE',
                'statusCode'  => '200',
                'logDate'     => new DateTimeImmutable('2022-01-01 12:00:00')
            ]
        );
        LogFactory::createMany(
            8,
            [
                'serviceName' => 'USER-SERVICE',
                'statusCode'  => '400',
                'logDate'     => new DateTimeImmutable('2022-01-02 00:00:00')
            ]
        );
        LogFactory::createMany(
            9,
            [
                'serviceName' => 'USER-SERVICE',
                'statusCode'  => '500',
                'logDate'     => new DateTimeImmutable('2022-01-03 23:59:59')
            ]
        );
    }
}
