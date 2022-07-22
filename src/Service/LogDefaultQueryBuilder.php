<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Log;
use Doctrine\ORM\QueryBuilder;

class LogDefaultQueryBuilder implements LogQueryBuilderInterface
{

    public const  LOG_TABLE_ALIAS      = "l";
    public const  LOG_COUNT_FIELD_NAME = "counter";

    public function resolveQueryBuilder(array $criteria, QueryBuilder $builder): QueryBuilder
    {
        return $builder->Select(
            sprintf('count(l.id) as %s ', self::LOG_COUNT_FIELD_NAME)
        )->from(
            Log::class,
            LogDefaultQueryBuilder::LOG_TABLE_ALIAS
        )->where('1=1');
    }
}
