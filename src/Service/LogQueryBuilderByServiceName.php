<?php

declare(strict_types=1);

namespace App\Service;

use App\Serializer\Encoder\LogDecoder;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;

class LogQueryBuilderByServiceName implements LogQueryBuilderInterface
{
    private LogQueryBuilderInterface $logSearchQueryBuilder;

    public function __construct(LogQueryBuilderInterface $logSearchQueryBuilder)
    {
        $this->logSearchQueryBuilder = $logSearchQueryBuilder;
    }

    public function resolveQueryBuilder(array $criteria, QueryBuilder $builder): QueryBuilder
    {
        $builder = $this->logSearchQueryBuilder->resolveQueryBuilder($criteria, $builder);
        if (array_key_exists(LogDecoder::SERVICE_NAME_KEY, $criteria)) {
            if ($criteria[LogDecoder::SERVICE_NAME_KEY] !== null) {
                $serviceNames = is_array($criteria[LogDecoder::SERVICE_NAME_KEY])
                    ? $criteria[LogDecoder::SERVICE_NAME_KEY]
                    : [$criteria[LogDecoder::SERVICE_NAME_KEY]];
                return $builder->andWhere(
                    sprintf(
                        "%s.%s in (:serviceName)",
                        LogDefaultQueryBuilder::LOG_TABLE_ALIAS,
                        LogDecoder::SERVICE_NAME_KEY
                    )
                )->setParameter(
                    'serviceName',
                    $serviceNames,
                    Connection::PARAM_STR_ARRAY
                );
            }
            /**
             * @todo if we wanna remove isNull from validator and handle it in code we should remove following commented line
             */
//            return $builder->andWhere(
//                sprintf("%s.%s IS NULL", LogDefaultQueryBuilder::LOG_TABLE_ALIAS, LogDecoder::SERVICE_NAME_KEY)
//            );
        }
        return $builder;
    }
}
