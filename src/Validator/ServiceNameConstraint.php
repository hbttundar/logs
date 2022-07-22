<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ServiceNameConstraint extends Constraint
{
    public $message = 'The serviceName in query string "{{ string }}" is invalid.';
    public $blank_message = 'The serviceName in query string can not be blank or null';

    public function validatedBy(): string
    {
        return ServiceNameValidator::class;
    }
}
