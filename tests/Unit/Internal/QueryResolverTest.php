<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Inputs\InputsByFiltersReceived;
use PhpCfdi\CfdiSatScraper\Internal\QueryResolver;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PhpCfdi\CfdiSatScraper\URLS;
use PHPUnit\Framework\MockObject\MockObject;

final class QueryResolverTest extends TestCase
{
    /** @var QueryResolver */
    private $resolver;

    /** @var SatHttpGateway&MockObject */
    private $satHttpGateway;

    protected function setUp(): void
    {
        parent::setUp();
        $this->satHttpGateway = $this->createMock(SatHttpGateway::class);
        $this->resolver = new QueryResolver($this->satHttpGateway);
    }

    public function testResolverConstruction(): void
    {
        $this->assertSame($this->satHttpGateway, $this->resolver->getSatHttpGateway());
    }

    public function testUrlFromDownloadType(): void
    {
        $this->assertSame(
            URLS::PORTAL_CFDI_CONSULTA_RECEPTOR,
            DownloadType::recibidos()->url(),
        );
        $this->assertSame(
            URLS::PORTAL_CFDI_CONSULTA_EMISOR,
            DownloadType::emitidos()->url(),
        );
    }

    public function testResolveQuery(): void
    {
        // prepare fake responses from SAT
        /** @var SatHttpGateway&MockObject $satHttpGateway */
        $satHttpGateway = $this->getMockBuilder(SatHttpGateway::class)
            ->disableOriginalConstructor()
            ->getMock();
        $satHttpGateway->method('getPortalPage')->willReturn(
            $this->fileContentPath('sample-response-receiver-form-page.html'),
        );
        $satHttpGateway->method('postAjaxSearch')->willReturn(
            $this->fileContentPath('sample-response-receiver-using-filters-initial.html'),
            $this->fileContentPath('sample-response-receiver-using-filters-search.html'),
        );

        // create resolver with prepared responses
        $resolver = new QueryResolver($satHttpGateway);

        // given a query (not important since it will not contact real server)
        $now = new DateTimeImmutable();
        $query = new QueryByFilters($now, $now, DownloadType::recibidos());
        $inputs = new InputsByFiltersReceived($query);

        // obtain results
        $list = $resolver->resolve($inputs);

        // check expected result count
        $this->assertCount(7, $list);
    }
}
