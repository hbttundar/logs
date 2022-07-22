<?php

declare(strict_types=1);

namespace App\Service;

use App\Serializer\Encoder\LogDecoder;
use Doctrine\ORM\QueryBuilder;

class LogQueryBuilderByStatusCode implements LogQueryBuilderInterface
{
    private LogQueryBuilderInterface $whereConditionBuilder;

    public function __construct(LogQueryBuilderInterface $whereConditionBuilder)
    {
        $this->whereConditionBuilder = $whereConditionBuilder;
    }

    public function resolveQueryBuilder(array $criteria, QueryBuilder $builder): QueryBuilder
    {
        $builder = $this->whereConditionBuilder->resolveQueryBuilder($criteria, $builder);
        if (array_key_exists(LogDecoder::STATUS_CODE_KEY, $criteria)) {
            if ($criteria[LogDecoder::STATUS_CODE_KEY] !== null) {
                return $builder->andWhere(
                    sprintf(
                        "%s.%s = :statusCode",
                        LogDefaultQueryBuilder::LOG_TABLE_ALIAS,
                        LogDecoder::STATUS_CODE_KEY
                    )
                )->setParameter(
                    'statusCode',
                    $criteria[LogDecoder::STATUS_CODE_KEY]
                );
            }
            /**
             * @todo if we wanna remove isNull from validator and handle it in code we should remove following commented line
             */
//            return $builder->andWhere(
//                sprintf("%s.%s IS NULL", LogDefaultQueryBuilder::LOG_TABLE_ALIAS, LogDecoder::STATUS_CODE_KEY)
//            );
        }
        return $builder;
    }
}
