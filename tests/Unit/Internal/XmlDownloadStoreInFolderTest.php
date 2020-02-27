<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use PhpCfdi\CfdiSatScraper\Contracts\XmlDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Exceptions\RuntimeException;
use PhpCfdi\CfdiSatScraper\Internal\XmlDownloadStoreInFolder;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

class XmlDownloadStoreInFolderTest extends TestCase
{
    public function testConstructWithDestinationFolder(): void
    {
        $downloader = new XmlDownloadStoreInFolder('foo');
        $this->assertInstanceOf(XmlDownloadHandlerInterface::class, $downloader);
        $this->assertSame('foo', $downloader->getDestinationFolder());
    }

    public function testConstructWithEmptyDestinationFolder(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('destination folder');
        new XmlDownloadStoreInFolder('');
    }

    public function testPathFor(): void
    {
        $downloader = new XmlDownloadStoreInFolder('foo');
        $this->assertSame('foo/uuid.xml', $downloader->pathFor('uuid'));
    }

    public function testCheckDestinationFolderWhenFolderExistsAndAskToNotCreate(): void
    {
        $downloader = new XmlDownloadStoreInFolder(__DIR__);
        /** @noinspection PhpUnhandledExceptionInspection */
        $downloader->checkDestinationFolder(false);
        $this->assertTrue(true, 'checkDestinationFolder should not create any exception');
    }

    public function testCheckDestinationFolderWhenFolderExistsAndAskToCreate(): void
    {
        $downloader = new XmlDownloadStoreInFolder(__DIR__);
        /** @noinspection PhpUnhandledExceptionInspection */
        $downloader->checkDestinationFolder(true);
        $this->assertTrue(true, 'checkDestinationFolder should not create any exception');
    }

    public function testCheckDestinationFolderWhenFolderNotExistsAndAskToNotCreate(): void
    {
        $destinationFolder = __DIR__ . '/non-existent';
        $downloader = new XmlDownloadStoreInFolder($destinationFolder);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not exists');
        /** @noinspection PhpUnhandledExceptionInspection */
        $downloader->checkDestinationFolder(false);
    }

    public function testCheckDestinationFolderWhenFolderIsNotAFolderAndAskToNotCreate(): void
    {
        $destinationFolder = __FILE__;
        $downloader = new XmlDownloadStoreInFolder($destinationFolder);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not exists');
        /** @noinspection PhpUnhandledExceptionInspection */
        $downloader->checkDestinationFolder(false);
    }

    public function testCheckDestinationFolderWhenFolderIsNotAFolderAndAskToCreate(): void
    {
        $destinationFolder = __FILE__;
        $downloader = new XmlDownloadStoreInFolder($destinationFolder);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is not a folder');
        /** @noinspection PhpUnhandledExceptionInspection */
        $downloader->checkDestinationFolder(true);
    }

    public function testCheckDestinationFolderWhenItDowsNotExistsAndAskToCreate(): void
    {
        $destinationFolder = $this->filePath(uniqid());
        try {
            $downloader = new XmlDownloadStoreInFolder($destinationFolder);
            /** @noinspection PhpUnhandledExceptionInspection */
            $downloader->checkDestinationFolder(true);
            $this->assertDirectoryExists($destinationFolder);
        } finally {
            if (is_dir($destinationFolder)) {
                rmdir($destinationFolder);
            }
        }
    }

    public function testOnSuccessStoresContentsToFile(): void
    {
        $destinationFolder = $this->filePath();
        $downloader = new XmlDownloadStoreInFolder($destinationFolder);

        $uuid = $this->fakes()->faker()->uuid;
        $expectedFile = $downloader->pathFor($uuid);

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @unlink($expectedFile);

        $contents = 'foo-bar';
        $response = $this->createMock(ResponseInterface::class);
        /** @noinspection PhpUnhandledExceptionInspection */
        $downloader->onSuccess($uuid, $contents, $response);

        try {
            $this->assertStringEqualsFile($expectedFile, $contents, '');
        } finally {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            @unlink($expectedFile);
        }
    }
}
