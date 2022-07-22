<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;


class StatusCodeValidator extends ConstraintValidator
{

    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof StatusCodeConstraint) {
            throw new UnexpectedTypeException($constraint, StatusCodeConstraint::class);
        }
        if (null === $value || '' === $value) {
            $this->context->buildViolation($constraint->blank_message)
                          ->addViolation();
            return;
        }
        $statusCode = (string)($value);
        if (!preg_match('/[2,3,4,5]\d{2}$/', $statusCode, $matches)) {
            $this->context->buildViolation($constraint->message)
                          ->setParameter('{{ string }}', $value)
                          ->addViolation();
        }
    }
}
