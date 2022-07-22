<?php

declare(strict_types=1);

namespace Tests\Controller;

use App\Factory\LogFactory;
use App\Test\ApiTestCase;
use DateTimeImmutable;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class LogControllerTest extends ApiTestCase
{

    use ReloadDatabaseTrait;

    private const BASE_URL = "/count";

    /** @test */
    public function it_can_visit()
    {
        $this->client->request('GET', self::BASE_URL);
        $this->assertResponseIsSuccessful();
    }

    /**
     * @test
     * @dataProvider   queryStringDataProvider
     */
    public function it_can_validate_query_string(string $queryString, string $message): void
    {
        $this->generateFakeData();
        $headers = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request('GET', self::BASE_URL . $queryString, [], [], $headers);
        $response     = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $this->assertResponseFormatSame('json');
        foreach ($responseData as $violation) {
            $this->assertContains($message, $violation);
        }
    }

    /** @test */
    public function it_can_fetch_count_of_logs()
    {
        LogFactory::createMany(20);
        $headers = array(
            'CONTENT_TYPE' => 'application/json',
        );
        $this->client->request('GET', self::BASE_URL, [], [], $headers);
        $response     = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $this->assertResponseFormatSame('json');
        $this->assertArrayHasKey("counter", $responseData);
        $this->assertSame(20, $responseData['counter']);
    }

    /**
     * @test
     * @dataProvider   filterDataProvider
     */
    public function it_can_fetch_count_of_logs_with_filter(
        string                   $fieldName,
        string|DateTimeImmutable $value,
        string                   $queryString,
        int                      $count
    ) {
        LogFactory::createMany($count, [$fieldName => $value]);
        $headers = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request('GET', self::BASE_URL . $queryString, [], [], $headers);
        $response     = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $this->assertResponseFormatSame('json');
        $this->assertArrayHasKey("counter", $responseData);
        $this->assertSame($count, $responseData['counter']);
    }

    /**
     * @test
     * @dataProvider   complexFilterDataProvider
     */
    public function it_can_handle_complex_filter(string $queryString, int $count): void
    {
        $this->generateFakeData();
        $headers = ['CONTENT_TYPE' => 'application/json'];
        $this->client->request('GET', self::BASE_URL . $queryString, [], [], $headers);
        $response     = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $this->assertResponseFormatSame('json');
        $this->assertArrayHasKey("counter", $responseData);
        $this->assertSame($count, $responseData['counter']);
    }

    private function filterDataProvider(): array
    {
        return [
            'USER-SERVICE'    => [
                'filedName'   => 'serviceName',
                'value'       => 'USER-SERVICE',
                'queryString' => "?serviceName=USER-SERVICE",
                'count'       => 10
            ],
            'INVOICE-SERVICE' => [
                'filedName'   => 'serviceName',
                'value'       => 'INVOICE-SERVICE',
                'queryString' => "?serviceName=INVOICE-SERVICE",
                'count'       => 5
            ],
            '200_STATUS_CODE' => [
                'filedName'   => 'statusCode',
                'value'       => '200',
                'queryString' => "?statusCode=200",
                'count'       => 5
            ],
            '400_STATUS_CODE' => [
                'filedName'   => 'statusCode',
                'value'       => '400',
                'queryString' => "?statusCode=400",
                'count'       => 4
            ],
            '300_STATUS_CODE' => [
                'filedName'   => 'statusCode',
                'value'       => '300',
                'queryString' => "?statusCode=300",
                'count'       => 8
            ],
            '500_STATUS_CODE' => [
                'filedName'   => 'statusCode',
                'value'       => '500',
                'queryString' => "?statusCode=500",
                'count'       => 3
            ],
            'START-DATE'      => [
                'filedName'   => 'logDate',
                'value'       => new DateTimeImmutable('2022-01-01 12:20:24'),
                'queryString' => sprintf("?startDate=%s", "2022-01-01 12:20:24"),
                'count'       => 10
            ],
            'END-DATE'        => [
                'filedName'   => 'logDate',
                'value'       => new DateTimeImmutable('2022-08-30 12:20:24'),
                'queryString' => sprintf("?endDate=%s", "2022-08-30 12:20:24"),
                'count'       => 7
            ],
        ];
    }

    private function complexFilterDataProvider(): array
    {
        return [
            'USER-SERVICE-INVOICE-SERVICE'             => [
                'queryString' => "?serviceName[]=USER-SERVICE&serviceName[]=INVOICE-SERVICE",
                'count'       => 23
            ],
            'USER-SERVICE-INVOICE-SERVICE-STATUS-CODE' => [
                'queryString' => "?serviceName[]=USER-SERVICE&serviceName[]=INVOICE-SERVICE&statusCode=200",
                'count'       => 6
            ],
            'USER-SERVICE-3'                           => [
                'queryString' => "?serviceName=USER-SERVICE&startDate=2022-01-01 12:00:00",
                'count'       => 20
            ],
            'INVOICE-SERVICE-3'                        => [
                'queryString' => "?serviceName=INVOICE-SERVICE&startDate=2022-01-01 12:00:00",
                'count'       => 3
            ],
            'USER-SERVICE-8'                           => [
                'queryString' => "?serviceName=USER-SERVICE&startDate=2022-01-02 00:00:00",
                'count'       => 17
            ],
            'USER-SERVICE-200-START_DATE'              => [
                'queryString' => "?serviceName=USER-SERVICE&statusCode=200&startDate=022-01-01 00:00:00",
                'count'       => 3
            ],

            'USER-SERVICE-START-END-DATE-1' => [
                'queryString' => "?serviceName=USER-SERVICE&startDate=2022-01-01 14:00:00&endDate=2022-01-01 23:59:59",
                'count'       => 0
            ],

            'USER-SERVICE-START-END-DATE-2' => [
                'queryString' => "?serviceName=USER-SERVICE&startDate=2022-01-01 14:00:00&endDate=2022-01-02 23:59:59",
                'count'       => 8
            ],


            'USER-SERVICE-STATUS-CODE-200' => [
                'queryString' => "?serviceName=USER-SERVICE&statusCode=200",
                'count'       => 3
            ],
            'USER-SERVICE-STATUS-CODE-400' => [
                'queryString' => "?serviceName=USER-SERVICE&statusCode=400",
                'count'       => 8
            ],
            'USER-SERVICE-STATUS-CODE-500' => [
                'queryString' => "?serviceName=USER-SERVICE&statusCode=500",
                'count'       => 9
            ],
        ];
    }

    private function queryStringDataProvider(): array
    {
        return [
            'BLANK-SERVICE-NAME'   => [
                'queryString' => "?serviceName=",
                'message'     => 'The serviceName in query string can not be blank or null'
            ],
            'INVALID-SERVICE-NAME' => [
                'queryString' => "?serviceName=1234",
                'message'     => 'The serviceName in query string "1234" is invalid.'
            ],
            'BLANK-STATUS-CODE'    => [
                'queryString' => "?statusCode=",
                'message'     => 'The statusCode in query string can not be blank or null'
            ],
            'INVALID-STATUS-CODE'  => [
                'queryString' => "?statusCode=5000",
                'message'     => 'The statusCode "5000" is invalid.'
            ],
            'BLANK-START-DATE'     => [
                'queryString' => "?startDate=",
                'message'     => 'The StartDate in query string can not be blank or null'
            ],
            'INVALID-START-DATE'   => [
                'queryString' => "?startDate=12345676",
                'message'     => 'The StartDate in query string "12345676" is invalid.'
            ],
            'BLANK-END-DATE'       => [
                'queryString' => "?endDate=",
                'message'     => 'The EndDate in query string can not be blank or null'
            ],
            'INVALID-END-DATE'     => [
                'queryString' => "?endDate=12345676",
                'message'     => 'The EndDate in query string "12345676" is invalid.'
            ],
        ];
    }
}
