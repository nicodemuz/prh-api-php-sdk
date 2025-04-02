<?php

declare(strict_types=1);

namespace Tests\Unit\Validator\Constraints;

use Nicodemuz\Prh\Validator\Constraints\FinnishBusinessId;
use Nicodemuz\Prh\Validator\Constraints\FinnishBusinessIdValidator;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class FinnishBusinessIdValidatorTest extends TestCase
{
    private FinnishBusinessIdValidator $validator;
    /** @var ExecutionContextInterface&MockObject */
    private ExecutionContextInterface $context;
    private FinnishBusinessId $constraint;

    #[Override]
    protected function setUp(): void
    {
        $this->validator = new FinnishBusinessIdValidator();
        $this->context = $this->createMock(ExecutionContextInterface::class);
        $this->constraint = new FinnishBusinessId();
        $this->validator->initialize($this->context);
    }

    public static function provideInvalidBusinessIds(): array
    {
        return [
            [12345678], // Invalid non-string value
            ['123456-7'], // Too short business ID
            ['1234567-9'], // Invalid check digit
            ['1000008-0'], // Invalid remainder
            ['1000008-1'], // Invalid remainder
            ['1000008-2'], // Invalid remainder
            ['1000008-3'], // Invalid remainder
            ['1000008-4'], // Invalid remainder
            ['1000008-5'], // Invalid remainder
            ['1000008-6'], // Invalid remainder
            ['1000008-7'], // Invalid remainder
            ['1000008-8'], // Invalid remainder
            ['1000008-9'], // Invalid remainder
        ];
    }

    public static function provideValidBusinessIds(): array
    {
        return [
            ['0250345-3'], // Valid business ID
            ['0000000-0'], // Valid business ID with check digit 0
            [null], // Null is valid, emptiness is validated by another constraint
            ['', ] // Empty string is valid, emptiness is validated by another constraint
        ];
    }

    #[DataProvider('provideInvalidBusinessIds')]
    public function testInvalidBusinessIds(string|int $businessId): void
    {
        $this->context->expects($this->once())
            ->method('buildViolation')
            ->with($this->constraint->message)
            ->willReturn($this->createMock(ConstraintViolationBuilderInterface::class));

        $this->validator->validate($businessId, $this->constraint);
    }

    #[DataProvider('provideValidBusinessIds')]
    public function testValidBusinessIds(?string $businessId): void
    {
        $this->context->expects($this->never())
            ->method('buildViolation');

        $this->validator->validate($businessId, $this->constraint);
    }
}
