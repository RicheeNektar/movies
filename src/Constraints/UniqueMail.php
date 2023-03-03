<?php

namespace App\Constraints;

use App\Validator\UniqueMailValidator;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueMail extends Constraint
{
    public string $message = "register.mail.not_unique";
    public string $mode = 'strict';

    public function __construct($options = null, array $groups = null, $payload = null)
    {
        parent::__construct($options ?? [], $groups, $payload);

        $this->message = $message ?? $this->message;
    }

    public function validatedBy(): string
    {
        return UniqueMailValidator::class;
    }
}