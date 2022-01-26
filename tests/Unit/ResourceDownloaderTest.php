<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use GuzzleHttp\Promise\PromiseInterface;
use PhpCfdi\CfdiSatScraper\Contracts\ResourceDownloaderPromiseHandlerInterface;
use PhpCfdi\CfdiSatScraper\Contracts\ResourceDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\LogicException;
use PhpCfdi\CfdiSatScraper\Internal\ResourceDownloaderPromiseHandler;
use PhpCfdi\CfdiSatScraper\Metadata;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\ResourceDownloader;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\Tests\Fakes\FakeResourceDownloaderPromiseHandler;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Traversable;

final class ResourceDownloaderTest extends TestCase
{
    private function createSatHttpGateway(): SatHttpGateway
    {
        return $this->createMock(SatHttpGateway::class);
    }

    public function testConstructDefaults(): void
    {
        $downloader = new ResourceDownloader($this->createSatHttpGateway(), ResourceType::xml());
        $this->assertFalse($downloader->hasMetadataList());
        $this->assertSame(ResourceDownloader::DEFAULT_CONCURRENCY, $downloader->getConcurrency());
    }

    public function testConstructWithValues(): void
    {
        $concurrency = ResourceDownloader::DEFAULT_CONCURRENCY + 10;
        $metadataList = new MetadataList([]);
        $downloader = new ResourceDownloader($this->createSatHttpGateway(), ResourceType::xml(), $metadataList, $concurrency);
        $this->assertTrue($downloader->hasMetadataList());
        $this->assertSame($metadataList, $downloader->getMetadataList());
        $this->assertSame($concurrency, $downloader->getConcurrency());
    }

    public function testGetMetadataListWithoutPropertySet(): void
    {
        $downloader = new ResourceDownloader($this->createSatHttpGateway(), ResourceType::xml());
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The metadata list has not been set');
        $downloader->getMetadataList();
    }

    public function testMetadataList(): void
    {
        $metadataList = new MetadataList([]);
        $downloader = new ResourceDownloader($this->createSatHttpGateway(), ResourceType::xml());
        $this->assertSame($downloader, $downloader->setMetadataList($metadataList), 'setMetadataList must be fluent');
        $this->assertSame($metadataList, $downloader->getMetadataList());
    }

    public function testSetConcurrency(): void
    {
        $downloader = new ResourceDownloader($this->createSatHttpGateway(), ResourceType::xml());
        $this->assertSame($downloader, $downloader->setConcurrency(100), 'setConcurrency must be fluent');
        $this->assertSame(100, $downloader->getConcurrency());
    }

    public function testSetConcurrencyLowerThanOne(): void
    {
        $downloader = new ResourceDownloader($this->createSatHttpGateway(), ResourceType::xml());
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
        $downloader = new class ($this->createSatHttpGateway(), ResourceType::xml()) extends ResourceDownloader {
            public function makePromises(): Traversable
            {
                return parent::makePromises();
            }
        };
        $metadataList = new MetadataList($medataArray);
        $downloader->setMetadataList($metadataList);

        $generated = iterator_to_array($downloader->makePromises());
        $this->assertContainsOnlyInstancesOf(PromiseInterface::class, $generated);
        $this->assertCount(2, $generated);
        $this->assertArrayHasKey($uuidWithUrlXml1, $generated);
        $this->assertArrayHasKey($uuidWithUrlXml2, $generated);
        $this->assertArrayNotHasKey($uuidWithoutLink, $generated);
    }

    public function testMakePromiseHandler(): void
    {
        $resourceType = ResourceType::cancelVoucher();
        $downloader = new class ($this->createSatHttpGateway(), $resourceType) extends ResourceDownloader {
            public function makePromiseHandler(ResourceDownloadHandlerInterface $handler): ResourceDownloaderPromiseHandlerInterface
            {
                return parent::makePromiseHandler($handler);
            }
        };

        $downloadHandler = $this->createMock(ResourceDownloadHandlerInterface::class);
        $promiseHandler = $downloader->makePromiseHandler($downloadHandler);
        $this->assertInstanceOf(ResourceDownloaderPromiseHandler::class, $promiseHandler);
        // put inside this if, otherwise code analysis will fail since getHandler is not part of interface
        if ($promiseHandler instanceof ResourceDownloaderPromiseHandler) {
            $this->assertSame($resourceType, $promiseHandler->getResourceType());
            $this->assertSame($downloadHandler, $promiseHandler->getHandler());
        }
    }

    public function testDownload(): void
    {
        // given this metadataList
        $metadataList = new MetadataList($this->createMetadataArrayUsingUuids(...[
            $uuidWithUrlXml0 = $this->fakes()->faker()->uuid,
            $this->fakes()->faker()->uuid,
            $uuidWithUrlXml2 = $this->fakes()->faker()->uuid,
        ]));

        // given a well known behavior for a fake ResourceDownloaderPromiseHandlerInterface
        // the behavior is that only even entries are downloaded, odd entries are rejected
        $downloader = new class (new SatHttpGateway(), ResourceType::xml()) extends ResourceDownloader {
            /** @noinspection PhpMissingParentCallCommonInspection */
            public function makePromiseHandler(ResourceDownloadHandlerInterface $handler): ResourceDownloaderPromiseHandlerInterface
            {
                return new FakeResourceDownloaderPromiseHandler();
            }
        };
        $downloader->setMetadataList($metadataList);
        $downloader->setConcurrency(1);

        // when running download
        /** @var ResourceDownloadHandlerInterface&MockObject $downloadHandler */
        $downloadHandler = $this->createMock(ResourceDownloadHandlerInterface::class);
        $downloaded = $downloader->download($downloadHandler);

        // then will get only the odd uuids
        $expectedUuids = [$uuidWithUrlXml0, $uuidWithUrlXml2];
        $this->assertSame($expectedUuids, $downloaded);
    }
}
