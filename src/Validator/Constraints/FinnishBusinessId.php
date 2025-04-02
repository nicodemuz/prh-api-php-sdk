<?php

declare(strict_types=1);

namespace Nicodemuz\Prh\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
final class FinnishBusinessId extends Constraint
{
    public string $message = 'The value "{{ value }}" is not a valid Finnish business ID.';
}
