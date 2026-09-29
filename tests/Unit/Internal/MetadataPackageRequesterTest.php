<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Exceptions\MetadataPackageException;
use PhpCfdi\CfdiSatScraper\Exceptions\PackageIsNotAvailableException;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Internal\MetadataPackageRequester;
use PhpCfdi\CfdiSatScraper\MetadataPackage\QueryResult;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\Tests\Internal\TemporaryFile;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PhpCfdi\CfdiSatScraper\URLS;
use PHPUnit\Framework\MockObject\MockObject;

final class MetadataPackageRequesterTest extends TestCase
{
    private const PACKAGE_UUID = '7C8D02B2-91A6-4AE0-801D-F05D5027939C';

    /** @var SatHttpGateway&MockObject */
    private MockObject $gateway;

    private MetadataPackageRequester $requester;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gateway = $this->createMock(SatHttpGateway::class);
        $this->requester = new MetadataPackageRequester($this->gateway);
    }

    public function testGatewayIsTheSameAsConstructor(): void
    {
        $this->assertSame($this->gateway, $this->requester->getGateway());
    }

    public function testExtractMetadataParametersReturnsEmptyWhenFieldIsNotPresent(): void
    {
        /**
         * File has Metadata but does not have hfParametrosMetadata
         * @see tests/_files/metadata-package-search-without-parameters.html
         */
        $html = $this->fileContentPath('metadata-package-search-without-parameters.html');
        $this->assertSame('', $this->requester->extractMetadataParameters($html));
    }

    public function testExtractMetadataParametersReturnsValueWhenFieldIsPresent(): void
    {
        // portal returns hfParametrosMetadata with value even when it has no results
        $html = $this->fileContentPath('metadata-package-search-without-metadata.html');
        $this->assertSame('PARAMETROS-METADATA-CODIFICADOS', $this->requester->extractMetadataParameters($html));
    }

    public function testExtractAvailablePackages(): void
    {
        $html = $this->fileContentPath('metadata-package-pending-packages.html');
        $pending = $this->requester->extractAvailablePackages($html);

        $this->assertCount(2, $pending);
        $this->assertTrue($pending->has(self::PACKAGE_UUID));
        $this->assertTrue($pending->has('1a2b3c4d-5e6f-4890-abcd-ef1234567890'));
        $this->assertSame('YmxvYlVyaVVubz09', $pending->get(self::PACKAGE_UUID)->getDownloadUrl());
    }

    public function testRequestPackage(): void
    {
        $this->gateway->method('getPortalPage')->willReturn(
            $this->fileContentPath('sample-response-receiver-form-page.html'),
        );
        $this->gateway->method('postAjaxSearch')->willReturn(
            $this->fileContentPath('sample-response-receiver-using-filters-initial.html'),
            $this->fileContentPath('metadata-package-search-with-metadata.html'),
        );
        $this->gateway->expects($this->once())
            ->method('postJson')
            ->with(URLS::PORTAL_CFDI_CONSULTA_RECEPTOR . '/DescargaMetadatos')
            ->willReturn(json_encode(['d' => 'Solicitud registrada con el folio de descarga: ' . self::PACKAGE_UUID]) ?: '');

        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-01 00:00:00'),
            new DateTimeImmutable('2026-01-31 23:59:59'),
            DownloadType::recibidos(),
        );

        $packageUuid = $this->requester->requestPackage($query);

        $this->assertSame(self::PACKAGE_UUID, $packageUuid);
    }

    public function testRequestPackageWithoutResultsThrowsException(): void
    {
        $this->gateway->method('getPortalPage')->willReturn(
            $this->fileContentPath('sample-response-receiver-form-page.html'),
        );
        $this->gateway->method('postAjaxSearch')->willReturn(
            $this->fileContentPath('sample-response-receiver-using-filters-initial.html'),
            $this->fileContentPath('metadata-package-search-without-metadata.html'),
        );

        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-01 00:00:00'),
            new DateTimeImmutable('2026-01-31 23:59:59'),
            DownloadType::recibidos(),
        );

        $this->expectException(MetadataPackageException::class);
        $this->requester->requestPackage($query);
    }

    public function testRequestPackageWithoutParametersThrowsException(): void
    {
        /**
         * File has Metadata but does not have hfParametrosMetadata
         * @see tests/_files/metadata-package-search-without-parameters.html
         */
        $this->gateway->method('getPortalPage')->willReturn(
            $this->fileContentPath('sample-response-receiver-form-page.html'),
        );
        $this->gateway->method('postAjaxSearch')->willReturn(
            $this->fileContentPath('sample-response-receiver-using-filters-initial.html'),
            $this->fileContentPath('metadata-package-search-without-parameters.html'),
        );

        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-01 00:00:00'),
            new DateTimeImmutable('2026-01-31 23:59:59'),
            DownloadType::recibidos(),
        );

        $this->expectException(MetadataPackageException::class);
        $this->requester->requestPackage($query);
    }

    public function testRequestPackageWithPortalErrorThrowsException(): void
    {
        $this->gateway->method('getPortalPage')->willReturn(
            $this->fileContentPath('sample-response-receiver-form-page.html'),
        );
        $this->gateway->method('postAjaxSearch')->willReturn(
            $this->fileContentPath('sample-response-receiver-using-filters-initial.html'),
            $this->fileContentPath('metadata-package-search-with-metadata.html'),
        );
        $this->gateway->method('postJson')
            ->willReturn(json_encode(['d' => 'Error: La solicitud no pudo ser procesada']) ?: '');

        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-01 00:00:00'),
            new DateTimeImmutable('2026-01-31 23:59:59'),
            DownloadType::recibidos(),
        );

        $this->expectException(MetadataPackageException::class);
        $this->requester->requestPackage($query);
    }

    public function testRequestPackagesSplitsReceivedMultiMonthQueryByMonth(): void
    {
        $this->gateway->method('getPortalPage')->willReturn(
            $this->fileContentPath('sample-response-receiver-form-page.html'),
        );
        // each month performs 2 ajax calls
        $this->gateway->method('postAjaxSearch')->willReturn(
            $this->fileContentPath('sample-response-receiver-using-filters-initial.html'),
            $this->fileContentPath('metadata-package-search-with-metadata.html'),
            $this->fileContentPath('sample-response-receiver-using-filters-initial.html'),
            $this->fileContentPath('metadata-package-search-with-metadata.html'),
            $this->fileContentPath('sample-response-receiver-using-filters-initial.html'),
            $this->fileContentPath('metadata-package-search-with-metadata.html'),
        );
        $this->gateway->method('postJson')->willReturn(
            json_encode(['d' => 'folio de descarga: 7C8D02B2-91A6-4AE0-801D-F05D5027939C']) ?: '',
            json_encode(['d' => 'folio de descarga: 1A2B3C4D-5E6F-4890-ABCD-EF1234567890']) ?: '',
            json_encode(['d' => 'folio de descarga: 2B3C4D5E-6F70-4901-BCDE-F12345678901']) ?: '',
        );

        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-01 00:00:00'),
            new DateTimeImmutable('2026-03-31 23:59:59'),
            DownloadType::recibidos(),
        );

        $packages = $this->requester->requestPackages($query);

        $this->assertCount(3, $packages);
        $this->assertTrue($packages->has('7c8d02b2-91a6-4ae0-801d-f05d5027939c'));
        $this->assertTrue($packages->has('1a2b3c4d-5e6f-4890-abcd-ef1234567890'));
        $this->assertTrue($packages->has('2b3c4d5e-6f70-4901-bcde-f12345678901'));
    }

    public function testRequestPackagesCatchesException(): void
    {
        $this->gateway->method('getPortalPage')->willReturn(
            $this->fileContentPath('sample-response-receiver-form-page.html'),
        );
        // each month performs 2 ajax calls
        $this->gateway->method('postAjaxSearch')->willReturn(
            $this->fileContentPath('sample-response-receiver-using-filters-initial.html'),
            $this->fileContentPath('metadata-package-search-with-metadata.html'),
            $this->fileContentPath('sample-response-receiver-using-filters-initial.html'),
            $this->fileContentPath('metadata-package-search-with-metadata.html'),
            $this->fileContentPath('sample-response-receiver-using-filters-initial.html'),
            $this->fileContentPath('metadata-package-search-without-metadata.html'),
        );
        $this->gateway->method('postJson')->willReturn(
            json_encode(['d' => 'folio de descarga: 7C8D02B2-91A6-4AE0-801D-F05D5027939C']) ?: '',
            json_encode(['d' => 'Error: La solicitud no pudo ser procesada']) ?: '',
            json_encode(['d' => '']) ?: '',
        );

        $query = new QueryByFilters(
            new DateTimeImmutable('2026-01-01 00:00:00'),
            new DateTimeImmutable('2026-03-31 23:59:59'),
            DownloadType::recibidos(),
        );

        /** @var list<QueryResult> $packages */
        $packages = iterator_to_array($this->requester->requestPackages($query));

        $this->assertCount(3, $packages);
        $this->assertSame('7C8D02B2-91A6-4AE0-801D-F05D5027939C', $packages[0]->getUuid());
        $this->assertInstanceOf(MetadataPackageException::class, $packages[1]->getException());
        $this->assertInstanceOf(MetadataPackageException::class, $packages[2]->getException());
    }

    public function testListAvailablePackages(): void
    {
        $this->gateway->method('getPortalPage')->willReturn(
            $this->fileContentPath('metadata-package-pending-packages.html'),
        );

        $pending = $this->requester->listAvailablePackages();

        $this->assertCount(2, $pending);
        $this->assertTrue($pending->has(self::PACKAGE_UUID));
    }

    public function testDownloadPackage(): void
    {
        $this->gateway->method('getPortalPage')->willReturn(
            $this->fileContentPath('metadata-package-pending-packages.html'),
        );

        $zipContents = "PK\x03\x04" . 'fake-zip-contents';
        $this->gateway->expects($this->once())
            ->method('postDownloadPackage')
            ->with(
                $this->callback(fn (array $fields): bool => ($fields['ctl00$MainContent$hfFolioDescargaActual'] ?? '') === self::PACKAGE_UUID
                    && ($fields['ctl00$MainContent$hfUrlDescargaActual'] ?? '') === 'YmxvYlVyaVVubz09'
                    && ($fields['__EVENTTARGET'] ?? '') === 'ctl00$MainContent$setLinkButtonDescarga'
                    // the hidden fields of the page must be preserved on the postback
                    && ($fields['__VIEWSTATE'] ?? '') === 'VIEWSTATE-VALUE'
                    && ($fields['__EVENTVALIDATION'] ?? '') === 'EVENTVALIDATION-VALUE'),
            )
            ->willReturn($zipContents);

        $temporaryFile = new TemporaryFile('metadata-package-test-');
        $this->requester->downloadPackage(self::PACKAGE_UUID, $temporaryFile->getPath());
        $this->assertSame($zipContents, $temporaryFile->getContents());
    }

    public function testDownloadPackageFromAvailable(): void
    {
        $this->gateway->method('getPortalPage')->willReturn(
            $this->fileContentPath('metadata-package-pending-packages.html'),
        );

        $zipContents = "PK\x03\x04" . 'fake-zip-contents';
        $this->gateway->expects($this->once())
            ->method('postDownloadPackage')
            ->with(
                $this->callback(fn (array $fields): bool => ($fields['ctl00$MainContent$hfFolioDescargaActual'] ?? '') === self::PACKAGE_UUID
                    && ($fields['ctl00$MainContent$hfUrlDescargaActual'] ?? '') === 'YmxvYlVyaVVubz09'
                    && ($fields['__EVENTTARGET'] ?? '') === 'ctl00$MainContent$setLinkButtonDescarga'
                    // the hidden fields of the page must be preserved on the postback
                    && ($fields['__VIEWSTATE'] ?? '') === 'VIEWSTATE-VALUE'
                    && ($fields['__EVENTVALIDATION'] ?? '') === 'EVENTVALIDATION-VALUE'),
            )
            ->willReturn($zipContents);

        $temporaryFile = new TemporaryFile('metadata-package-test-');
        $available = $this->requester->listAvailablePackages();
        $this->requester->downloadPackageFromAvailable($available, self::PACKAGE_UUID, $temporaryFile->getPath());
        $this->assertSame($zipContents, $temporaryFile->getContents());
    }

    public function testDownloadPackageWithHtmlResponseThrowsException(): void
    {
        $this->gateway->method('getPortalPage')->willReturn(
            $this->fileContentPath('metadata-package-pending-packages.html'),
        );
        // the portal returns a html page instead of the zip when the postback is not valid
        $this->gateway->method('postDownloadPackage')->willReturn(
            '<!DOCTYPE html><html lang="es"><body>Error</body></html>',
        );

        $temporaryFile = new TemporaryFile('metadata-package-test-');
        $this->expectException(MetadataPackageException::class);
        $this->requester->downloadPackage(self::PACKAGE_UUID, $temporaryFile->getPath());
    }

    public function testDownloadPackageNotAvailableThrowsException(): void
    {
        $this->gateway->method('getPortalPage')->willReturn(
            $this->fileContentPath('metadata-package-pending-packages.html'),
        );
        $temporaryFile = new TemporaryFile();

        $exception = null;
        $packageUuid = 'ffffffff-ffff-ffff-ffff-ffffffffffff';
        try {
            $this->requester->downloadPackage($packageUuid, $temporaryFile->getPath());
        } catch (PackageIsNotAvailableException $e) {
            $exception = $e;
        }
        $this->assertInstanceOf(PackageIsNotAvailableException::class, $exception);
        $this->assertSame($packageUuid, $exception->getUuid());
    }
}
