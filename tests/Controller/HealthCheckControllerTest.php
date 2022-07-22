<?php

declare(strict_types=1);

namespace Tests\Controller;


use App\Test\ApiTestCase;

class HealthCheckControllerTest extends ApiTestCase
{
    public function testHealthZEndpoint(): void
    {
        $this->client->request('GET', '/healthz');
        $this->assertResponseIsSuccessful();
    }
}
