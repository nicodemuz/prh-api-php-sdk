
# PRH API PHP SDK

A  PHP client library for accessing company data from the Finnish Patent and Registration Office (PRH) Open Data API using Finnish business IDs (Y-tunnus).

## Features
- Retrieve company data by business ID, including:
  - Company name(s)
  - Website
  - Current address (street, city, postal code)
  - Main line of business (code and description)
- Strong input validation for Finnish business IDs using Symfony Validator
- PSR-18 HTTP client compatibility for flexible HTTP requests
- Comprehensive error handling for API responses
- Unit and functional tests with PHPUnit
- Static analysis with Psalm for code quality

## Requirements
- PHP 8.4 or higher
- Composer
- Dependencies:
  - `psr/http-client` (PSR-18 HTTP client implementation)
  - `psr/http-factory` (PSR-17 request factory implementation)

## Usage

```php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use GuzzleHttp\Client as HttpClient;
use Nicodemuz\Prh\PrhClient;
use Nicodemuz\Prh\Request\CompaniesRequest;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\Validator\Exception\ValidationFailedException;

$httpClient = new HttpClient();
$requestFactory = new Psr17Factory();
$prhClient = new PrhClient($httpClient, $requestFactory);

try {
    $companiesRequest = new CompaniesRequest(
        businessId: '0250345-3',
    );

    $response = $prhClient->getCompanies($companiesRequest);

    $companies = $response->getCompanies();

    if (isset($companies[0])) {
        $company = $companies[0];

        echo "Company name: " . $company->getCurrentName() . PHP_EOL;
        echo "Company website: " . $company->getWebsite() . PHP_EOL;
        echo "Company current address: " . $company->getCurrentStreet() . ", " . $company->getCurrentPostalCode() . " " . $company->getCurrentCity() . PHP_EOL;
        echo "Company current mainline of business: " . $company->getMainBusinessLineCode() . ' (' . $company->getMainBusinessLineDescription() . ')' . PHP_EOL;
    } else {
        echo "Could not find any company for given request." . PHP_EOL;
    }
} catch (ValidationFailedException $e) {
    echo "Invalid request: " . $e->getMessage() . PHP_EOL;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}
```


## Running Tests
1. Ensure test dependencies are installed:
   ```bash
   composer install --dev
   ```
2. Run PHPUnit tests:
   ```bash
   vendor/bin/phpunit tests
   ```
3. Run Psalm for static analysis:
   ```bash
   vendor/bin/psalm
   ```
4. Run PHPStan for static analysis:
   ```bash
   vendor/bin/phpstan analyse
   ```
