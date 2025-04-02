<?php

declare(strict_types=1);

namespace Nicodemuz\Prh\Response;

use Nicodemuz\Prh\Model\Company;

final class CompaniesResponse
{
    /** @var array<Company> */
    private array $companies = [];

    public function __construct(array $data)
    {
        foreach ($data['companies'] as $company) {
            $this->companies[] = Company::fromArray($company);
        }
    }

    /**
     * @return array<Company>
     */
    public function getCompanies(): array
    {
        return $this->companies;
    }
}
