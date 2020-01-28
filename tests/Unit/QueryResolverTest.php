<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use PhpCfdi\CfdiSatScraper\Filters\FiltersIssued;
use PhpCfdi\CfdiSatScraper\Filters\FiltersReceived;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Query;
use PhpCfdi\CfdiSatScraper\QueryResolver;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PhpCfdi\CfdiSatScraper\URLS;
use PHPUnit\Framework\MockObject\MockObject;

final class QueryResolverTest extends TestCase
{
    /** @var QueryResolver */
    private $resolver;

    /** @var Client&MockObject */
    private $client;

    /** @var CookieJar&MockObject */
    private $cookie;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = $this->createMock(Client::class);
        $this->cookie = $this->createMock(CookieJar::class);
        $this->resolver = new QueryResolver($this->client, $this->cookie);
    }

    public function testResolverConstruction(): void
    {
        $this->assertSame($this->client, $this->resolver->getClient());
        $this->assertSame($this->cookie, $this->resolver->getCookie());
    }

    public function testFiltersFromQuery(): void
    {
        $now = new \DateTimeImmutable();
        $this->assertInstanceOf(FiltersIssued::class, $this->resolver->filtersFromQuery(
            (new Query($now, $now))->setDownloadType(DownloadTypesOption::emitidos())
        ));
        $this->assertInstanceOf(FiltersReceived::class, $this->resolver->filtersFromQuery(
            (new Query($now, $now))->setDownloadType(DownloadTypesOption::recibidos())
        ));
    }

    public function testUrlFromDownloadType(): void
    {
        $this->assertSame(
            URLS::SAT_URL_PORTAL_CFDI_CONSULTA_RECEPTOR,
            $this->resolver->urlFromDownloadType(DownloadTypesOption::recibidos())
        );
        $this->assertSame(
            URLS::SAT_URL_PORTAL_CFDI_CONSULTA_EMISOR,
            $this->resolver->urlFromDownloadType(DownloadTypesOption::emitidos())
        );
    }

    public function testResolveQuery(): void
    {
        // prepare resolver mocking responses from SAT
        /** @var QueryResolver&MockObject $resolver */
        $resolver = $this->getMockBuilder(QueryResolver::class)
            ->disableOriginalConstructor()
            ->setMethods(['consumeFormPage', 'consumeSearch'])
            ->getMock();
        $resolver->method('consumeFormPage')->willReturn(
            $this->fileContentPath('sample-response-receiver-form-page.html')
        );
        $resolver->method('consumeSearch')->willReturn(
            $this->fileContentPath('sample-response-receiver-using-filters-initial.html'),
            $this->fileContentPath('sample-response-receiver-using-filters-search.html')
        );

        // given a query (not important since it will not contact real server)
        $now = new \DateTimeImmutable();
        $query = (new Query($now, $now))->setDownloadType(DownloadTypesOption::recibidos());

        // obtain results
        $list = $resolver->resolve($query);

        // check expected result count
        $this->assertCount(7, $list);
    }
}
