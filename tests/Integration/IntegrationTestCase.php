<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use DOMAttr;
use DOMDocument;
use DOMNodeList;
use DOMXPath;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Metadata;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\SATScraper;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

class IntegrationTestCase extends TestCase
{
    /**
     * @var Factory
     * @internal
     */
    protected static $factory;

    protected function getFactory(): Factory
    {
        if (null === static::$factory) {
            static::$factory = new Factory(__DIR__ . '/../repository.json');
        }
        return static::$factory;
    }

    protected function getSatScraper(): SATScraper
    {
        try {
            return $this->getFactory()->getSatScraper();
        } catch (\RuntimeException $exception) {
            $this->markTestSkipped($exception->getMessage());
            throw $exception;
        }
    }

    protected function getRepository(): Repository
    {
        try {
            return $this->getFactory()->getRepository();
        } catch (\RuntimeException $exception) {
            $this->markTestSkipped($exception->getMessage());
            throw $exception;
        }
    }

    public function providerEmitidosRecibidos(): array
    {
        return [
            'recibidos' => [DownloadTypesOption::recibidos()],
            'emitidos' => [DownloadTypesOption::emitidos()],
        ];
    }

    public static function assertRepositoryEqualsMetadataList(Repository $repository, MetadataList $list): void
    {
        /** @var RepositoryItem $item */
        foreach ($repository as $item) {
            $metadata = $list->get($item->getUuid());
            self::assertNotNull($metadata, "The metadata list does not contain the UUID {$item->getUuid()}");
            self::assertRepositoryItemEqualsMetadata($item, $metadata);
        }
        self::assertCount(count($repository), $list, 'The metadata list has not the same quantity of elements');
    }

    public static function assertRepositoryItemEqualsMetadata(RepositoryItem $item, Metadata $metadata): void
    {
        self::assertSame($item->getUuid(), $metadata->uuid(), 'The metadata UUID does not match');

        $metadataState = $metadata->get('estadoComprobante');
        self::assertSame($item->getState(), strtoupper(substr($metadataState, 0, 1)), 'The metadata state does not match');

        $metadataDateTime = new \DateTimeImmutable($metadata->get('fechaEmision'));
        self::assertEquals($item->getDate(), $metadataDateTime, 'The metadata date does not match');
    }

    public static function assertCfdiHasUuid(string $expectedUuid, string $xmlCfdi): void
    {
        self::assertNotEmpty($xmlCfdi, 'The XML CFDI is empty');
        $document = new DOMDocument();
        $document->loadXML($xmlCfdi);
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('cfdi', 'http://www.sat.gob.mx/cfd/3');
        $xpath->registerNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');
        /** @var DOMNodeList<DOMAttr>|false $list */
        $list = $xpath->query('/cfdi:Comprobante/cfdi:Complemento/tfd:TimbreFiscalDigital/@UUID');
        if (false === $list) {
            $list = new DOMNodeList();
        }
        self::assertLessThan(2, $list->count(), 'The XML CFDI have more than one UUID');
        self::assertGreaterThan(0, $list->count(), 'The XML CFDI does not have an UUID');
        /** @var DOMAttr $uuidAttr */
        $uuidAttr = $list->item(0);
        self::assertSame(
            $expectedUuid,
            $uuidAttr->value,
            sprintf('The UUID from the XML CFDI %s is not the same as expected %s', $uuidAttr->value, $expectedUuid)
        );
    }

    public function getDownloadTypeText(DownloadTypesOption $downloadType): string
    {
        return $downloadType->isEmitidos() ? 'emitidos' : 'recibidos';
    }
}
