<?php

namespace Nicodemuz\Prh\Validator\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class FinnishBusinessIdValidator extends ConstraintValidator
{
    #[Override]
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof FinnishBusinessId) {
            throw new UnexpectedTypeException($constraint, FinnishBusinessId::class);
        }

        if ($value === null || $value === '') {
            return;
        }

        if (!is_string($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $this->formatValue($value))
                ->addViolation();
            return;
        }

        // Check format: 7 digits, hyphen, 1 digit (e.g., "1234567-8")
        if (!preg_match('/^\d{7}-\d$/', $value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
            return;
        }

        if (!$this->isValidCheckDigit($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }

    private function isValidCheckDigit(string $businessId): bool
    {
        // Remove the hyphen to process digits
        $digits = str_replace('-', '', $businessId);
        $number = substr($digits, 0, 7); // First 7 digits
        $checkDigit = (int) $digits[7];  // Last digit

        // Weights for the check digit calculation
        $weights = [7, 9, 10, 5, 8, 4, 2];
        $sum = 0;

        // Calculate the weighted sum
        for ($i = 0; $i < 7; $i++) {
            $sum += (int) $number[$i] * $weights[$i];
        }

        // Modulo 11
        $remainder = $sum % 11;

        // Check digit logic:
        // - If remainder is 0, check digit must be 0
        // - If remainder is 1, the ID is invalid (not used)
        // - Otherwise, check digit is 11 - remainder
        if ($remainder === 0) {
            return $checkDigit === 0;
        }
        if ($remainder === 1) {
            return false; // Invalid case
        }

        return $checkDigit === (11 - $remainder);
    }
}
