<?php

namespace App\Constraints;

use App\Validator\UniqueUsernameValidator;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueUsername extends Constraint
{
    public string $message = "This username cannot be used. User already exists.";
    public string $mode = 'strict';

    public function validatedBy(): string
    {
        return UniqueUsernameValidator::class;
    }
}