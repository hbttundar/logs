<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\LogDefaultQueryBuilder;
use App\Service\LogQueryStringValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LogController extends BaseController
{
    #[Route('/count', methods: ['GET'])]
    public function __invoke(
        Request                 $request,
        LogQueryStringValidator $validator
    ): Response {
        $query = $request->getQueryString();
        if (!$query) {
            return $this->createApiResponse(
                [LogDefaultQueryBuilder::LOG_COUNT_FIELD_NAME => $this->getLogRepository()->count([])]
            );
        }
        $queryParams = $this->getQueryParams($query);
        $violations  = $validator->validate($queryParams);
        if (count($violations) > 0) {
            return $this->createApiErrorResponse($violations);
        }
        $result = $this->getLogRepository()->getLogCount($queryParams);
        return $this->createApiResponse($result);
    }

    private function getQueryParams(string $query): array
    {
        $result = [];
        /**
         * @todo If multiple fields of the same name exist in a query string PHP overwrite them.
         *       PHP uses a non-standards compliant practice of including brackets in fieldnames
         *       to achieve the same effect if you need to use standard approach instead of bracket
         *       uncomment line 50 and proper_parse_str function
         */
        parse_str($query, $result);
        #$this->proper_parse_str($_SERVER['QUERY_STRING'], $result);
        return $result;
    }

//    private function proper_parse_str($query, array &$result)
//    {
//        $pairs = explode('&', $query);
//        foreach ($pairs as $i) {
//            list($name, $value) = explode('=', $i,);
//            /** remove brackets in name if exist any  */
//            $name = str_replace('[]', '', $name);
//            if (isset($result[$name])) {
//                if (is_array($result[$name])) {
//                    $result[$name][] = $value;
//                } else {
//                    $result[$name] = array($result[$name], $value);
//                }
//            } else {
//                $result[$name] = $value;
//            }
//        }
//    }

}
