<?php

declare(strict_types=1);

namespace App\Validator;

use DateTimeImmutable;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

abstract class LogDateValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof LogDateConstraint) {
            throw new UnexpectedTypeException($constraint, LogDateConstraint::class);
        }
        if (null === $value || '' === $value) {
            $this->context->buildViolation($constraint->blank_message)
                          ->addViolation();
            return;
        }
        if (is_string($value)) {
            try {
                new DateTimeImmutable($value);
            } catch (\Exception) {
                $this->context->buildViolation($constraint->message)
                              ->setParameter('{{ string }}', $value)
                              ->addViolation();
            }
            return;
        }

        if (!is_a(DateTimeImmutable::class, $value)) {
            $this->context->buildViolation($constraint->message)
                          ->setParameter('{{ string }}', $value)
                          ->addViolation();
        }
    }
}
