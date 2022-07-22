<?php

declare(strict_types=1);

namespace App\Service;

use App\Serializer\Encoder\LogDecoder;
use App\Validator\EndDateConstraint;
use App\Validator\ServiceNameConstraint;
use App\Validator\StartDateConstraint;
use App\Validator\StatusCodeConstraint;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LogQueryStringValidator
{
    private ValidatorInterface $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @return ConstraintViolationListInterface[]
     */
    public function validate(array $query): array
    {
        $validatedItems = [];
        foreach ($query as $key => $value) {
            $violation = match (true) {
                $key === LogQueryBuilderByStartDate::START_DATE_KEY => $this->validator->validate(
                    $value,
                    [new StartDateConstraint()]
                ),
                $key === LogQueryBuilderByEndDate::END_DATE_KEY => $this->validator->validate(
                    $value,
                    [new EndDateConstraint()]
                ),
                $key === LogDecoder::SERVICE_NAME_KEY => $this->validator->validate(
                    $value,
                    [new ServiceNameConstraint()]
                ),
                $key === LogDecoder::STATUS_CODE_KEY => $this->validator->validate(
                    $value,
                    [new StatusCodeConstraint()]
                )
            };
            if ($violation->count() > 0) {
                $validatedItems[] = $violation;
            }
        }
        return $validatedItems;
    }
}
