<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Log;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[AsCommand(
    name: 'logs Importer',
    description: 'using this command you can pass log files for import log to system',
    aliases: ['log:import'],
    hidden: false
)]
class LogImportCommand extends Command
{
    protected static $defaultName = 'log:import';

    private const IMPORT_BATCH_SIZE = 100;

    private SerializerInterface    $serializer;
    private EntityManagerInterface $entityManager;
    private CacheInterface         $cacheService;
    private string                 $logFile;
    private ?int                   $fromIndex;
    private ?int                   $toIndex;


    public function __construct(
        EntityManagerInterface $entityManager,
        SerializerInterface    $serializer,
        CacheInterface         $cacheService
    ) {
        $this->serializer    = $serializer;
        $this->entityManager = $entityManager;
        $this->cacheService  = $cacheService;
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to create a import log files to system')
             ->addArgument(
                 'log_file',
                 InputArgument::REQUIRED,
                 'please provide the log file which you wanna import shipments from it'
             )->addOption(
                'from',
                'f',
                InputOption::VALUE_OPTIONAL,
                "this option indicate which index of provided log file is the first one which you wanna import to database"
                . " this options in combination with to option provide a facility for you to import range of data"
                . " if you have a huge amount of data for import"
                . " for example you can write command like this shipment:import xxx.txt -f 0 -t 19"
                . " which means you wanna import first 20 items of data to db."
            )->addOption(
                'to',
                't',
                InputOption::VALUE_OPTIONAL,
                'this option indicate which index of provided shipments is the last one that you wanna import to database'
                . " this options in combination with from option provide a facility for you to import range of data"
                . " if you have a huge amount of data for import"
                . " for example you can write command like this shipment:import xxx.txt -f 0 -t 19"
                . " which means you wanna import first 20 items of data to db."
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io              = new SymfonyStyle($input, $output);
        $this->logFile   = $input->getArgument('log_file');
        $this->fromIndex = $input->getOption('from');
        $this->toIndex   = $input->getOption('to');
        if (!file_exists($this->logFile)) {
            $io->note(sprintf('the file you passed as a shipments file: %s not found', $this->logFile));
            return Command::INVALID;
        }
        $io->info(
            'start to import shipments data into the database,it may take times based on your data amount, so please be patient!'
        );
        try {
            $logs = $this->cacheService->get(
                'log_data' . md5(pathinfo($this->logFile, PATHINFO_FILENAME)),
                function () {
                    return $this->serializer->deserialize(
                        $this->logFile,
                        'App\Entity\Log[]',
                        'log'
                    );
                }
            );
            $this->importLogs($logs, $output);
        } catch (\Doctrine\DBAL\Exception|InvalidArgumentException $e) {
            $io->Error(sprintf('something went wong!!!! , please try again later:[%s]', $e->getMessage()));
        } catch (Exception $e) {
            $io->Error(sprintf('please provide a valid log file to import logs:[%s]', $e->getMessage()));
            throw new BadRequestException(
                sprintf('please provide a valid log file to import logs:[%s]', $e->getMessage())
            );
        }
        $io->newLine();
        $io->success('importing logs data successfully finished.');

        return Command::SUCCESS;
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Doctrine\DBAL\Exception
     */
    private function importLogs(
        array           $logs,
        OutputInterface $output
    ): void {
        $batchIndex  = 1;
        $from        = $this->fromIndex ?? 0;
        $to          = $this->toIndex ?? count($logs) - 1;
        $progressBar = new ProgressBar($output, $to - $from);
        for ($i = $from; $i <= $to; $i++) {
            $log       = $logs[$i];
            $cacheKey  = md5(serialize($log));
            $isInCache = $this->cacheService->hasItem($cacheKey);
            ++$batchIndex;
            if (!$isInCache) {
                $this->cachedAndPersistLogData(
                    $log,
                    $batchIndex,
                    $progressBar,
                    $cacheKey,
                    $this->entityManager->getConnection()
                );
            }
        }
    }

    /**
     * @throws InvalidArgumentException
     * @throws \Doctrine\DBAL\Exception
     */
    private function cachedAndPersistLogData(
        Log         $log,
        int         $batchIndex,
        ProgressBar $progressBar,
        string      $cacheKey,
        Connection  $connection
    ): void {
        $connection->beginTransaction();
        try {
            $this->entityManager->persist($log);
            if (($batchIndex % self::IMPORT_BATCH_SIZE === 0)) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
            $this->entityManager->flush();
            $progressBar->advance();
            $this->cacheService->get($cacheKey, function () use ($log) {
                return $log;
            });
            $connection->commit();
        } catch (Exception) {
            $this->cacheService->delete($cacheKey);
            $connection->rollBack();
        }
    }
}
