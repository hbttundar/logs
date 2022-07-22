<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class LogQueryBuilderService
{


    private QueryBuilder $builder;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->builder = $entityManager->createQueryBuilder();
    }


    public function resolveQueryBuilder(array $criteria): QueryBuilder
    {
        $defaultSearchService = new LogDefaultQueryBuilder();
        $searchByServiceName  = new LogQueryBuilderByServiceName($defaultSearchService);
        $searchByStatusCode   = new LogQueryBuilderByStatusCode($searchByServiceName);
        $searchByStartDate    = new LogQueryBuilderByStartDate($searchByStatusCode);
        $searchByEndDate      = new LogQueryBuilderByEndDate($searchByStartDate);
        return $searchByEndDate->resolveQueryBuilder($criteria, $this->builder);
    }
}
