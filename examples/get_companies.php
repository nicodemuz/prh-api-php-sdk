<?php

declare(strict_types=1);

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
