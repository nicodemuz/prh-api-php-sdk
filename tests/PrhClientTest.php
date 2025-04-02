<?php

declare(strict_types=1);

namespace Tests\Functional;

use Nicodemuz\Prh\Model\Company;
use Nicodemuz\Prh\PrhClient;
use Nicodemuz\Prh\Request\CompaniesRequest;
use Nicodemuz\Prh\Response\CompaniesResponse;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class PrhClientTest extends TestCase
{
    /** @var ClientInterface&MockObject */
    private ClientInterface $httpClient;
    /** @var RequestFactoryInterface&MockObject */
    private RequestFactoryInterface $requestFactory;
    private PrhClient $prhClient;

    #[Override]
    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->requestFactory = $this->createMock(RequestFactoryInterface::class);
        $this->prhClient = new PrhClient($this->httpClient, $this->requestFactory);
    }

    public function testGetCompaniesSuccess(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')
            ->willReturnSelf();

        $responseBody = $this->createMock(StreamInterface::class);
        $responseBody->method('__toString')
            ->willReturn(file_get_contents(__DIR__ . '/_fixtures/0250345-3.json'));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')
            ->willReturn(200);
        $response->method('getBody')
            ->willReturn($responseBody);

        $this->requestFactory->method('createRequest')
            ->with('GET', 'https://avoindata.prh.fi/opendata-ytj-api/v3/companies?businessId=0250345-3')
            ->willReturn($request);

        $this->httpClient->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $companiesRequest = new CompaniesRequest(businessId: '0250345-3');

        $result = $this->prhClient->getCompanies($companiesRequest);

        $this->assertInstanceOf(CompaniesResponse::class, $result);
        $this->assertEquals(1, sizeof($result->getCompanies()));
        $company = current($result->getCompanies());
        $this->assertInstanceOf(Company::class, $company);
        $this->assertEquals('0250345-3', $company->getBusinessId());
    }

    public function testGetCompaniesWithEmptyParams(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')
            ->willReturnSelf();

        $responseBody = $this->createMock(StreamInterface::class);
        $responseBody->method('__toString')
            ->willReturn(file_get_contents(__DIR__ . '/_fixtures/page1.json'));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')
            ->willReturn(200);
        $response->method('getBody')
            ->willReturn($responseBody);

        $this->requestFactory->method('createRequest')
            ->with('GET', 'https://avoindata.prh.fi/opendata-ytj-api/v3/companies')
            ->willReturn($request);

        $this->httpClient->method('sendRequest')
            ->with($request)
            ->willReturn($response);

        $companiesRequest = new CompaniesRequest();

        $result = $this->prhClient->getCompanies($companiesRequest);

        $this->assertInstanceOf(CompaniesResponse::class, $result);
        $this->assertEquals(100, sizeof($result->getCompanies()));
    }

    public function testGetCompaniesWithInvalidBusinessId(): void
    {
        $companiesRequest = new CompaniesRequest(businessId: '1234567-9');

        $this->expectException(ValidationFailedException::class);
        $this->expectExceptionMessage('The value "1234567-9" is not a valid Finnish business ID.');

        $this->prhClient->getCompanies($companiesRequest);
    }
}
