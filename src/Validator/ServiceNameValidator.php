<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ServiceNameValidator extends ConstraintValidator
{
    private const SERVICE_LIST = ['USER-SERVICE', 'INVOICE-SERVICE'];

    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof ServiceNameConstraint) {
            throw new UnexpectedTypeException($constraint, ServiceNameConstraint::class);
        }
        if (null === $value || '' === $value) {
            $this->context->buildViolation($constraint->blank_message)
                          ->addViolation();
            return;
        }
        if (!is_string($value) && !is_array($value)) {
            throw new UnexpectedValueException($value, 'string|array');
        }
        if (is_array($value)) {
            foreach ($value as $serviceName) {
                if (null === $serviceName || '' === $serviceName) {
                    $this->context->buildViolation($constraint->blank_message)
                                  ->addViolation();
                    return;
                }
                if (!is_string($serviceName)) {
                    throw new UnexpectedValueException($serviceName, 'string');
                }
                if (!in_array($serviceName, self::SERVICE_LIST)) {
                    $this->context->buildViolation($constraint->message)
                                  ->setParameter('{{ string }}', $serviceName)
                                  ->addViolation();
                }
            }
            return;
        }
        if (!in_array($value, self::SERVICE_LIST)) {
            $this->context->buildViolation($constraint->message)
                          ->setParameter('{{ string }}', $value)
                          ->addViolation();
        }
    }
}
