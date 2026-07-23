<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class NoHtml extends Constraint
{
    public const string PATTERN = '/<\s*[a-z!\/][^>]*>/i';

    public string $message = 'form.review.no_html';

    public function validatedBy(): string
    {
        return NoHtmlValidator::class;
    }
}
