<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Log;
use App\Service\LogQueryBuilderService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<Log>
 *
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{

    private LogQueryBuilderService $logQueryBuilder;

    public function __construct(ManagerRegistry $registry, LogQueryBuilderService $logQueryBuilder)
    {
        parent::__construct($registry, Log::class);
        $this->logQueryBuilder = $logQueryBuilder;
    }

    public function add(Log $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Log $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getLogCount(array $criteria)
    {
        return $this->logQueryBuilder->resolveQueryBuilder($criteria)->getQuery()->getScalarResult()[0];
    }

}
