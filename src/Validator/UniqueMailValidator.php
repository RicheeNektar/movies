<?php

namespace App\Validator;

use App\Constraints\UniqueMail;
use App\Constraints\UniqueUsername;
use App\Repository\UserMailRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UniqueMailValidator extends ConstraintValidator
{
    private UserMailRepository $userMailRepository;

    public function __construct(
        UserMailRepository $userMailRepository
    ) {
        $this->userMailRepository = $userMailRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UniqueMail) {
            throw new UnexpectedTypeException($constraint, UniqueUsername::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if ($this->userMailRepository->findOneBy([
                'mail' => $value,
            ]) !== null
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}