<?php

declare(strict_types=1);

namespace Nicodemuz\Prh\Request;

use Nicodemuz\Prh\Validator\Constraints\FinnishBusinessId;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CompaniesRequest
{
    public function __construct(
        #[Assert\Sequentially([
            new Assert\Length(min: 9, max: 9, exactMessage: 'The business ID must be exactly 9 characters long (e.g., "1234567-8").'),
            new FinnishBusinessId(),
        ])]
        private ?string $businessId = null
    ) {
    }

    public function toArray(): array
    {
        return array_filter([
            'businessId' => $this->businessId,
        ]);
    }
}
