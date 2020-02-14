<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;

class RetrieveByUuidTest extends IntegrationTestCase
{
    public function providerRetrieveByUuid(): array
    {
        return [
            'recibidos, random 1' => [DownloadTypesOption::recibidos(), 8],
            'emitidos, random 1' => [DownloadTypesOption::emitidos(), 1],
            'recibidos, random 4' => [DownloadTypesOption::recibidos(), 3],
            'emitidos, random 4' => [DownloadTypesOption::emitidos(), 3],
        ];
    }

    /**
     * @param DownloadTypesOption $downloadType
     * @param int $count
     * @dataProvider providerRetrieveByUuid
     */
    public function testRetrieveByUuid(DownloadTypesOption $downloadType, int $count): void
    {
        $typeText = $this->getDownloadTypeText($downloadType);
        $repository = $this->getRepository()->filterByType($downloadType);
        $repository = $repository->randomize()->topItems($count);
        $uuids = $repository->getUuids();
        $minimal = (1 === $count) ? 1 : 2;
        if (count($uuids) < $minimal) {
            $this->markTestSkipped(
                sprintf('It should be at least %d UUID on the repository (type %s)', $minimal, $typeText)
            );
        }

        $scraper = $this->getSatScraper();
        $list = $scraper->downloadListUUID($uuids, $downloadType);
        foreach ($uuids as $uuid) {
            $this->assertTrue($list->has($uuid), "The UUID $uuid was not found in the metadata list $typeText");
        }
        $this->assertCount(count($uuids), $list, sprintf('It was expected to receive only %d records', count($uuids)));

        $tempDir = sys_get_temp_dir();
        foreach ($uuids as $uuid) {
            $filename = strtolower(sprintf('%s/%s.xml', $tempDir, $uuid));
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
        $scraper->downloader($list)->saveTo($tempDir);
        foreach ($repository->getIterator() as $uuid => $item) {
            $filename = strtolower(sprintf('%s/%s.xml', $tempDir, $uuid));
            if ('Cancelado' !== $item->getState()) {
                $this->assertFileNotExists($filename, sprintf('The cfdi file with uuid %s does not exists: %s', $uuid, $filename));
            } else {
                $this->assertFileExists($filename, sprintf('The cfdi file with uuid %s does not exists: %s', $uuid, $filename));
                $this->assertCfdiHasUuid($uuid, file_get_contents($filename) ?: '');
            }
        }
    }
}
