<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class NoHtmlValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoHtml) {
            throw new UnexpectedTypeException($constraint, NoHtml::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (1 === preg_match(NoHtml::PATTERN, $value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
