<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\QueryBuilder;

interface LogQueryBuilderInterface
{
    public function resolveQueryBuilder(array $criteria, QueryBuilder $builder): QueryBuilder;
}
