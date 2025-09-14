<?php

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;

class RetrieveByUuidTest extends IntegrationTestCase
{
    /** @return array<string, array{DownloadType, int}> */
    public static function providerRetrieveByUuid(): array
    {
        return [
            'recibidos, random 1' => [DownloadType::recibidos(), 1],
            'emitidos, random 1' => [DownloadType::emitidos(), 1],
            'recibidos, random 10' => [DownloadType::recibidos(), 10],
            'emitidos, random 10' => [DownloadType::emitidos(), 10],
        ];
    }

    #[DataProvider('providerRetrieveByUuid')]
    public function testRetrieveByUuid(DownloadType $downloadType, int $count): void
    {
        // set up
        $resourceType = ResourceType::xml();
        $typeText = $this->getDownloadTypeText($downloadType);
        $repository = $this->getRepository()->filterByType($downloadType);
        $repository = $repository->randomize()->topItems($count);
        $uuids = $repository->getUuids();
        $minimal = (1 === $count) ? 1 : 2;
        if (count($uuids) < $minimal) {
            $this->markTestSkipped(
                sprintf('It should be at least %d UUID on the repository (type %s)', $minimal, $typeText),
            );
        }

        // check that all uuids exists and don't have more
        $scraper = $this->getSatScraper();
        $list = $scraper->listByUuids($uuids, $downloadType);
        foreach ($uuids as $uuid) {
            $this->assertTrue($list->has($uuid), "The UUID $uuid was not found in the metadata list $typeText");
        }
        $this->assertCount(count($uuids), $list, sprintf('It was expected to receive only %d records', count($uuids)));

        // just use items that have a download link
        $list = $list->filterWithResourceLink($resourceType);

        // clean destination
        $tempDir = sys_get_temp_dir() . '/cfdi-sat-scraper/retrieve-by-uuid';
        shell_exec(sprintf('rm -rf %s', escapeshellarg($tempDir)));
        shell_exec(sprintf('mkdir -p %s', escapeshellarg($tempDir)));

        // perform download
        $this->downloadWithRetry($scraper, $resourceType, $list, $tempDir);

        // check file existence
        foreach ($repository->getIterator() as $uuid => $item) {
            $filename = sprintf('%s/%s.xml', $tempDir, strtolower($uuid));
            if (! $list->has($uuid)) {
                $this->assertFileDoesNotExist($filename, sprintf('The cfdi file with uuid %s should not exists: %s', $uuid, $filename));
            } else {
                $this->assertFileExists($filename, sprintf('The cfdi file with uuid %s should exists: %s', $uuid, $filename));
                $this->assertCfdiHasUuid($uuid, file_get_contents($filename) ?: '');
            }
        }
    }

    private function downloadWithRetry(SatScraper $scraper, ResourceType $resourceType, MetadataList $list, string $destination): void
    {
        $maxAttempts = 10;
        $attempt = 1;
        $downloader = $scraper->resourceDownloader($resourceType);

        $list = $list->filterWithResourceLink($resourceType);
        while ($list->count() > 0) {
            $downloader->setMetadataList($list);
            $downloaded = $downloader->saveTo($destination);
            $list = $list->filterWithOutUuids($downloaded);
            $attempt = $attempt + 1;
            if ($attempt === $maxAttempts) {
                print_r(['Missing' => $list]);
                throw new RuntimeException(sprintf('Unable to domplete download after %s attempts', $attempt));
            }
        }
    }
}
