<?php

declare(strict_types=1);

namespace Nicodemuz\Prh;

use Exception;
use Nicodemuz\Prh\Request\CompaniesRequest;
use Nicodemuz\Prh\Response\CompaniesResponse;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class PrhClient
{
    private string $baseUrl = 'https://avoindata.prh.fi/opendata-ytj-api/v3';
    private ValidatorInterface $validator;

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory
    ) {
        $this->validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    public function getCompanies(CompaniesRequest $request): CompaniesResponse
    {
        $violations = $this->validator->validate($request);
        if (count($violations) > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getPropertyPath() . ': ' . (string)$violation->getMessage();
            }
            throw new ValidationFailedException('Invalid request: ' . implode(', ', $messages), $violations);
        }

        $response = $this->request('GET', '/companies', $request->toArray());
        return new CompaniesResponse($response);
    }

    private function request(string $method, string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl . $endpoint;

        if (sizeof($params) > 0) {
            $url .= '?' . http_build_query($params);
        }

        $request = $this->requestFactory->createRequest($method, $url)
            ->withHeader('Accept', 'application/json')
            ->withHeader('Content-Type', 'application/json')
        ;

        $response = $this->httpClient->sendRequest($request);

        return $this->handleResponse($response);
    }

    private function handleResponse(ResponseInterface $response): array
    {
        $statusCode = $response->getStatusCode();
        $body = (string)$response->getBody();

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new Exception(sprintf('API request failed with status code %s: %s', $statusCode, $body));
        }

        return json_decode($body, true);
    }
}
