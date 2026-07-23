<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Validator\Constraints\NoHtml;
use App\Validator\Constraints\NoHtmlValidator;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

/**
 * @extends ConstraintValidatorTestCase<NoHtmlValidator>
 */
final class NoHtmlValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator(): NoHtmlValidator
    {
        return new NoHtmlValidator();
    }

    public function testAcceptsNullAndEmptyValues(): void
    {
        $this->validateValue(null);
        $this->assertNoViolation();

        $this->validateValue('');
        $this->assertNoViolation();
    }

    public function testAcceptsPlainText(): void
    {
        $this->validateValue('Reliable service and helpful support.');

        $this->assertNoViolation();
    }

    public function testRejectsHtmlTags(): void
    {
        $this->validateValue('<strong>Unsafe markup</strong>');

        $this->buildViolation('form.review.no_html')
            ->setInvalidValue('<strong>Unsafe markup</strong>')
            ->assertRaised();
    }

    public function testRejectsNonStringValues(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validateValue(123);
    }

    public function testRejectsUnexpectedConstraintType(): void
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->validator->validate('plain text', new Length(min: 1));
    }

    private function validateValue(mixed $value): void
    {
        $constraint = new NoHtml();
        $this->constraint = $constraint;
        $this->setValue($value);
        $this->context->setConstraint($constraint);
        $this->validator->validate($value, $constraint);
    }
}
