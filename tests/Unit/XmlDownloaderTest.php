<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use GuzzleHttp\Promise\PromiseInterface;
use PhpCfdi\CfdiSatScraper\Contracts\XmlDownloaderPromiseHandlerInterface;
use PhpCfdi\CfdiSatScraper\Contracts\XmlDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\Internal\XmlDownloaderPromiseHandler;
use PhpCfdi\CfdiSatScraper\Metadata;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\Tests\Fakes\FakeXmlDownloaderPromiseHandler;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PhpCfdi\CfdiSatScraper\XmlDownloader;
use PHPUnit\Framework\MockObject\MockObject;
use Traversable;

final class XmlDownloaderTest extends TestCase
{
    private function createSatHttpGateway(): SatHttpGateway
    {
        return $this->createMock(SatHttpGateway::class);
    }

    public function testConstructDefaults(): void
    {
        $downloader = new XmlDownloader($this->createSatHttpGateway());
        $this->assertFalse($downloader->hasMetadataList());
        $this->assertSame(XmlDownloader::DEFAULT_CONCURRENCY, $downloader->getConcurrency());
    }

    public function testConstructWithValues(): void
    {
        $concurrency = XmlDownloader::DEFAULT_CONCURRENCY + 10;
        $metadataList = new MetadataList([]);
        $downloader = new XmlDownloader($this->createSatHttpGateway(), $metadataList, $concurrency);
        $this->assertTrue($downloader->hasMetadataList());
        $this->assertSame($metadataList, $downloader->getMetadataList());
        $this->assertSame($concurrency, $downloader->getConcurrency());
    }

    public function testGetMetadataListWithoutPropertySet(): void
    {
        $downloader = new XmlDownloader($this->createSatHttpGateway());
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The metadata list has not been set');
        $downloader->getMetadataList();
    }

    public function testMetadataList(): void
    {
        $metadataList = new MetadataList([]);
        $downloader = new XmlDownloader($this->createSatHttpGateway());
        $this->assertSame($downloader, $downloader->setMetadataList($metadataList), 'setMetadataList must be fluent');
        $this->assertSame($metadataList, $downloader->getMetadataList());
    }

    public function testSetConcurrency(): void
    {
        $downloader = new XmlDownloader($this->createSatHttpGateway());
        $this->assertSame($downloader, $downloader->setConcurrency(100), 'setConcurrency must be fluent');
        $this->assertSame(100, $downloader->getConcurrency());
    }

    public function testSetConcurrencyLowerThanOne(): void
    {
        $downloader = new XmlDownloader($this->createSatHttpGateway());
        $downloader->setConcurrency(0);
        $this->assertSame(1, $downloader->getConcurrency());
    }

    public function testMakePromises(): void
    {
        $medataArray = [
                $uuidWithoutLink = $this->fakes()->faker()->uuid => new Metadata($uuidWithoutLink),
            ] + $this->createMetadataArrayUsingUuids(...[
                $uuidWithUrlXml1 = $this->fakes()->faker()->uuid,
                $uuidWithUrlXml2 = $this->fakes()->faker()->uuid,
            ]);
        $metadataList = new MetadataList($medataArray);
        $downloader = new class($this->createSatHttpGateway(), $metadataList) extends XmlDownloader {
            public function makePromises(): Traversable
            {
                return parent::makePromises();
            }
        };

        $generated = iterator_to_array($downloader->makePromises());
        $this->assertContainsOnlyInstancesOf(PromiseInterface::class, $generated);
        $this->assertCount(2, $generated);
        $this->assertArrayHasKey($uuidWithUrlXml1, $generated);
        $this->assertArrayHasKey($uuidWithUrlXml2, $generated);
        $this->assertArrayNotHasKey($uuidWithoutLink, $generated);
    }

    public function testMakePromiseHandler(): void
    {
        $downloader = new class($this->createSatHttpGateway()) extends XmlDownloader {
            public function makePromiseHandler(XmlDownloadHandlerInterface $handler): XmlDownloaderPromiseHandlerInterface
            {
                return parent::makePromiseHandler($handler);
            }
        };

        $downloadHandler = $this->createMock(XmlDownloadHandlerInterface::class);
        $promiseHandler = $downloader->makePromiseHandler($downloadHandler);
        $this->assertInstanceOf(XmlDownloaderPromiseHandler::class, $promiseHandler);
        // put inside this if, otherwise code analysis will fail since getHandler is not part of interface
        if ($promiseHandler instanceof XmlDownloaderPromiseHandler) {
            $this->assertSame($downloadHandler, $promiseHandler->getHandler());
        }
    }

    public function testDownload(): void
    {
        // given this metadataList
        $metadataList = new MetadataList($this->createMetadataArrayUsingUuids(...[
            $uuidWithUrlXml1 = $this->fakes()->faker()->uuid,
            $uuidWithUrlXml2 = $this->fakes()->faker()->uuid,
            $uuidWithUrlXml3 = $this->fakes()->faker()->uuid,
        ]));

        // given a well known behavior for a fake XmlDownloaderPromiseHandlerInterface
        $downloader = new class(new SatHttpGateway(), $metadataList, 1) extends XmlDownloader {
            public function makePromiseHandler(XmlDownloadHandlerInterface $handler): XmlDownloaderPromiseHandlerInterface
            {
                return new FakeXmlDownloaderPromiseHandler();
            }
        };

        // when running download
        /** @var XmlDownloadHandlerInterface|MockObject $downloadHandler */
        $downloadHandler = $this->createMock(XmlDownloadHandlerInterface::class);
        $downloaded = $downloader->download($downloadHandler);

        // then will get only the odd uuids
        $expectedUuids = [$uuidWithUrlXml1, $uuidWithUrlXml3];
        $this->assertSame($expectedUuids, $downloaded);
    }
}
