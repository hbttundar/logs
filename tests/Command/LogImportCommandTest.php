<?php

declare(strict_types=1);

namespace Tests\Command;

use App\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class LogImportCommandTest extends KernelTestCase
{
    use ReloadDatabaseTrait;

    private CommandTester $logImportTester;

    protected function setUp(): void
    {
        self::$kernel          = $this->bootKernel();
        $application           = new Application(self::$kernel);
        $command               = $application->find('log:import');
        $this->logImportTester = new CommandTester($command);
        $this->cacheClear();
    }

    /** @test */
    public function it_can_import_log_text_file(): void
    {
        $log_file = "tests/Helper/logs.txt";
        $this->logImportTester->execute(
            [
                "log_file" => $log_file
            ]
        );
        $count = $this->getEntityManager()->getRepository(Log::class)->count([]);
        $this->assertSame(20, $count);
    }

    /** @test */
    public function it_can_import_log_text_file_chunk_by_chunk(): void
    {
        $log_file = "tests/Helper/logs.txt";
        $this->logImportTester->execute(
            [
                "log_file" => $log_file,
                '--from'   => 0,
                '--to'     => 9
            ]
        );
        $count = $this->getEntityManager()->getRepository(Log::class)->count([]);
        $this->assertSame(10, $count);
    }

    /** @test */
    public function it_start_from_where_interrupted()
    {
        $log_file = "tests/Helper/logs.txt";
        $this->logImportTester->execute(
            [
                "log_file" => $log_file,
                '--from'   => 0,
                '--to'     => 9
            ]
        );
        $this->logImportTester->execute(
            [
                "log_file" => $log_file,
                '--from'   => 0,
                '--to'     => 9
            ]
        );
        $count = $this->getEntityManager()->getRepository(Log::class)->count([]);
        $this->assertSame(10, $count);
        $this->logImportTester->execute(
            [
                "log_file" => $log_file,
            ]
        );
        $count = $this->getEntityManager()->getRepository(Log::class)->count([]);
        $this->assertSame(20, $count);
    }

    private function cacheClear()
    {
        $cacheService = $this->getContainer()->get('cache.app');
        $cacheService->clear();
    }

    private function getEntityManager(): EntityManagerInterface
    {
        return self::$kernel->getContainer()->get('doctrine')->getManager();
    }
}
