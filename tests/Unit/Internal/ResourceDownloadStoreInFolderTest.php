<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use PhpCfdi\CfdiSatScraper\Contracts\ResourceDownloadHandlerInterface;
use PhpCfdi\CfdiSatScraper\Contracts\ResourceFileNamerInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Exceptions\RuntimeException;
use PhpCfdi\CfdiSatScraper\Internal\ResourceDownloadStoreInFolder;
use PhpCfdi\CfdiSatScraper\Internal\ResourceFileNamerByType;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use Psr\Http\Message\ResponseInterface;

class ResourceDownloadStoreInFolderTest extends TestCase
{
    private function createResourceDownloadStoreInFolder(string $folder, ResourceFileNamerInterface $namer = null): ResourceDownloadStoreInFolder
    {
        /** @var ResourceFileNamerInterface $namer */
        $namer = $namer ?? $this->createMock(ResourceFileNamerInterface::class);
        return new ResourceDownloadStoreInFolder($folder, $namer);
    }

    public function testConstructWithDestinationFolder(): void
    {
        $destinationFolder = 'foo';
        $downloader = $this->createResourceDownloadStoreInFolder($destinationFolder);
        $this->assertInstanceOf(ResourceDownloadHandlerInterface::class, $downloader);
        $this->assertSame($destinationFolder, $downloader->getDestinationFolder());
        $this->assertInstanceOf(ResourceFileNamerInterface::class, $downloader->getResouceFileNamer());
    }

    public function testConstructWithEmptyDestinationFolder(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('destination folder');
        $this->createResourceDownloadStoreInFolder('');
    }

    public function testPathFor(): void
    {
        $namer = new ResourceFileNamerByType(ResourceType::xml());
        $downloader = $this->createResourceDownloadStoreInFolder('foo', $namer);
        $this->assertSame('foo/uuid.xml', $downloader->pathFor('uuid'));
    }

    public function testCheckDestinationFolderWhenFolderExistsAndAskToNotCreate(): void
    {
        $downloader = $this->createResourceDownloadStoreInFolder(__DIR__);
        $downloader->checkDestinationFolder(false);
        $this->assertTrue(true, 'checkDestinationFolder should not create any exception');
    }

    public function testCheckDestinationFolderWhenFolderExistsAndAskToCreate(): void
    {
        $downloader = $this->createResourceDownloadStoreInFolder(__DIR__);
        $downloader->checkDestinationFolder(true);
        $this->assertTrue(true, 'checkDestinationFolder should not create any exception');
    }

    public function testCheckDestinationFolderWhenFolderNotExistsAndAskToNotCreate(): void
    {
        $downloader = $this->createResourceDownloadStoreInFolder(__DIR__ . '/non-existent');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not exists');
        $downloader->checkDestinationFolder(false);
    }

    public function testCheckDestinationFolderWhenFolderIsNotAFolderAndAskToNotCreate(): void
    {
        $downloader = $this->createResourceDownloadStoreInFolder(__FILE__);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('does not exists');
        $downloader->checkDestinationFolder(false);
    }

    public function testCheckDestinationFolderWhenFolderIsNotAFolderAndAskToCreate(): void
    {
        $downloader = $this->createResourceDownloadStoreInFolder(__FILE__);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is not a folder');
        $downloader->checkDestinationFolder(true);
    }

    public function testCheckDestinationFolderWhenItDowsNotExistsAndAskToCreate(): void
    {
        $destinationFolder = $this->filePath(uniqid());
        try {
            $downloader = $this->createResourceDownloadStoreInFolder($destinationFolder);
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
        $namer = new ResourceFileNamerByType(ResourceType::xml());
        $downloader = $this->createResourceDownloadStoreInFolder($destinationFolder, $namer);

        $uuid = $this->fakes()->faker()->uuid;
        $expectedFile = $downloader->pathFor($uuid);

        /** @noinspection PhpUsageOfSilenceOperatorInspection */
        @unlink($expectedFile);

        $contents = 'foo-bar';
        $response = $this->createMock(ResponseInterface::class);
        $downloader->onSuccess($uuid, $contents, $response);

        try {
            $this->assertStringEqualsFile($expectedFile, $contents);
        } finally {
            /** @noinspection PhpUsageOfSilenceOperatorInspection */
            @unlink($expectedFile);
        }
    }
}
