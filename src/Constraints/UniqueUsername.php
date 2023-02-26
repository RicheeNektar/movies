<?php

namespace App\Constraints;

use App\Validator\UniqueUsernameValidator;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueUsername extends Constraint
{
    public string $message = "register.username.not_unique";
    public string $mode = 'strict';

    public function __construct($options = null, array $groups = null, $payload = null)
    {
        parent::__construct($options ?? [], $groups, $payload);

        $this->message = $message ?? $this->message;
    }

    public function validatedBy(): string
    {
        return UniqueUsernameValidator::class;
    }
}