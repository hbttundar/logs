<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\QueryBuilder;

class LogQueryBuilderByStartDate implements LogQueryBuilderInterface
{

    public const START_DATE_KEY        = 'startDate';
    private const START_DATE_FIELD_NAME = 'logDate';

    private LogQueryBuilderInterface $whereConditionBuilder;

    public function __construct(LogQueryBuilderInterface $whereConditionBuilder)
    {
        $this->whereConditionBuilder = $whereConditionBuilder;
    }

    public function resolveQueryBuilder(array $criteria, QueryBuilder $builder): QueryBuilder
    {
        $builder = $this->whereConditionBuilder->resolveQueryBuilder($criteria, $builder);
        if (array_key_exists(self::START_DATE_KEY, $criteria)) {
            if ($criteria[self::START_DATE_KEY] !== null) {
                return $builder->andWhere(
                    sprintf(
                        "%s.%s >= :startDate",
                        LogDefaultQueryBuilder::LOG_TABLE_ALIAS,
                        self::START_DATE_FIELD_NAME
                    )
                )->setParameter(
                    'startDate',
                    $criteria[self::START_DATE_KEY]
                );
            }
            /**
             * @todo if we wanna remove isNull from validator and handle it in code we should remove following commented line
             */
//            return $builder->andWhere(
//                sprintf("%s.%s IS NULL", LogDefaultQueryBuilder::LOG_TABLE_ALIAS, self::START_DATE_FIELD_NAME)
//            );
        };
        return $builder;
    }
}
