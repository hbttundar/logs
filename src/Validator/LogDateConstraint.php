<?php

declare(strict_types=1);

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

abstract class LogDateConstraint  extends Constraint
{
    public $message = 'The {{ string }} in query string "{{ string }}" is invalid.';
    public $blank_message = 'The {{ string }} in query string can not be blank or null';

    public function validatedBy(): string
    {
        return LogDateValidator::class;
    }
}
