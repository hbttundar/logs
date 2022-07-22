<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\ORM\QueryBuilder;

class LogQueryBuilderByEndDate implements LogQueryBuilderInterface
{

    public const  END_DATE_KEY        = 'endDate';
    private const END_DATE_FIELD_NAME = 'logDate';

    private LogQueryBuilderInterface $whereConditionBuilder;

    public function __construct(LogQueryBuilderInterface $whereConditionBuilder)
    {
        $this->whereConditionBuilder = $whereConditionBuilder;
    }

    public function resolveQueryBuilder(array $criteria, QueryBuilder $builder): QueryBuilder
    {
        $builder = $this->whereConditionBuilder->resolveQueryBuilder($criteria, $builder);
        if (array_key_exists(self::END_DATE_KEY, $criteria)) {
            if ($criteria[self::END_DATE_KEY] !== null) {
                return $builder->andWhere(
                    sprintf(
                        "%s.%s <= :endDate",
                        LogDefaultQueryBuilder::LOG_TABLE_ALIAS,
                        self::END_DATE_FIELD_NAME,
                    )
                )->setParameter(
                    'endDate',
                    $criteria[self::END_DATE_KEY]
                );
            }
            return $builder->andWhere(
                sprintf("%s.%s IS NULL", LogDefaultQueryBuilder::LOG_TABLE_ALIAS, self::END_DATE_FIELD_NAME)
            );
        }
        return $builder;
    }

}
