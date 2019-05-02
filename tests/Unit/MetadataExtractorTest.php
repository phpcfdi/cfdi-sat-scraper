<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\MetadataExtractor;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class MetadataExtractorTest extends TestCase
{
    public function testLocateHeaderFieldsInRow(): void
    {
        $fieldsCaptions = [
            'uuid' => 'Folio Fiscal',
            'foo' => 'Foo Foo Foo',
            'rfcEmisor' => 'RFC Emisor',
        ];

        $firstRow = (new Crawler(
            '<tr><th>Alpha</th><th>Folio Fiscal</th><th>RFC Emisor</th></tr>'
        ))->filter('tr')->first();
        $extractor = new MetadataExtractor();
        $fieldsPositions = $extractor->locateFieldsPositions($firstRow, $fieldsCaptions);

        $expectedFieldsPositions = [
            'uuid' => 1,
            'rfcEmisor' => 2,
        ];

        $this->assertSame($expectedFieldsPositions, $fieldsPositions);
    }

    public function testObtainMetadataValuesPredefined(): void
    {
        $fieldsPositions = [
            'foo' => 3, // delta
            'bar' => 1, // bravo
        ];

        $row = (new Crawler(
            '<tr><td>Alpha</td><td>Bravo</td><td>Charlie</td><td>Delta</td><td>Echo</td></tr>'
        ))->filter('tr')->first();
        $extractor = new MetadataExtractor();
        $values = $extractor->obtainMetadataValues($row, $fieldsPositions);

        $expectedValues = [
            'foo' => 'Delta',
            'bar' => 'Bravo',
        ];

        $this->assertSame($expectedValues, $values);
    }

    public function testObtainMetadataValuesWithEmptyRow(): void
    {
        $row = (new Crawler('<tr></tr>'))->filter('tr')->first();
        $extractor = new MetadataExtractor();
        $values = $extractor->obtainMetadataValues($row, ['uuid' => 1]);
        $this->assertArrayHasKey('uuid', $values);
        $this->assertSame('', $values['uuid']);
    }

    public function testObtainUrlWithoutButton(): void
    {
        $row = (new Crawler('<tr></tr>'))->filter('tr')->first();
        $extractor = new MetadataExtractor();
        $this->assertNull($extractor->obtainUrlXml($row));
    }

    public function testUsingFakeInputWithTenUuids(): void
    {
        $sample = $this->fileContentPath('fake-to-extract-metadata-ten-cfdi.html');
        $extractor = new MetadataExtractor();
        $list = $extractor->extract($sample);
        $this->assertCount(10, $list);

        foreach (range(1, 10) as $i) {
            $uuid = sprintf('B97262E5-704C-4BF7-AE26-%012d', $i);
            $this->assertTrue($list->has($uuid));
        }
    }

    public function testUsingFakeInputWithZeroUuids(): void
    {
        $sample = $this->fileContentPath('fake-to-extract-metadata-zero-cfdi.html');
        $extractor = new MetadataExtractor();
        $list = $extractor->extract($sample);
        $this->assertCount(0, $list);
    }

    public function testUsingFakeInput(): void
    {
        $sample = $this->fileContentPath('fake-to-extract-metadata-one-cfdi.html');
        $extractor = new MetadataExtractor();
        $list = $extractor->extract($sample);
        $this->assertCount(1, $list);
        $expectedUuid = 'B97262E5-704C-4BF7-AE26-9174FEF04D63';
        $this->assertTrue($list->has($expectedUuid));
    }

    public function testExtractUsingSampleWithOneUuid(): void
    {
        // this sample file contains 1 UUID only
        $sample = $this->fileContentPath('sample-to-extract-metadata-one-cfdi.html');

        $extractor = new MetadataExtractor();
        $data = $extractor->extract($sample);
        $this->assertCount(1, $data);

        $expectedUuid = 'B97262E5-704C-4BF7-AE26-9174FEF04D63';

        $expectedData = [
            $expectedUuid => [
                'uuid' => $expectedUuid,
                'rfcEmisor' => 'BSM970519DU8',
                'nombreEmisor' => 'BANCO SANTANDER MEXICO,'
                    . ' SA INSTITUCION DE BANCA MULTIPLE, GRUPO FINANCIERO SANTANDER MEXICO',
                'rfcReceptor' => 'AUAC920422D38',
                'nombreReceptor' => 'CESAR RENE AGUILERA ARREOLA',
                'fechaEmision' => '2019-03-31T02:04:46',
                'fechaCertificacion' => '2019-03-31T02:05:15',
                'pacCertifico' => 'INT020124V62',
                'total' => '$0.00',
                'efectoComprobante' => 'Ingreso',
                'estatusCancelacion' => 'Cancelable sin aceptación',
                'estadoComprobante' => 'Vigente',
                'estatusProcesoCancelacion' => '',
                'fechaProcesoCancelacion' => '',
                'urlXml' => 'https://portalcfdi.facturaelectronica.sat.gob.mx/RecuperaCfdi.aspx'
                    . '?Datos=M1peaCnvSPWnQLHcYL1G+TfX+fycHbwuKHo7GloSoS6fqnGuUFQ9RSqJcwdD4F5kspeWgLtl'
                    . '/vgh+6fWBSRdELCsFI/nXD8HCOfiBTzcb0iW9LMYb3Se0U+ftfc6WC8xKL3ikJOv4JS5YVwJEdUvGup'
                    . '1HJedFqaFw7EhVDA3Fxr/Jt/RUKRldWR9pQXtzJmNNNAvNuuQ1WbbhtvZIjJw28l01rtr34ZqjKKWQB'
                    . 'zCDqWNVYFLmllZb1kLWWn9MtAkh/RqjfgaHuLlhhk8UTPQAjjyOrZ56ePLhIWK2ujfGbLORXeKe4dsu'
                    . 'ykG3oF7Fxr/YjCpA9dGyuRJxYkVwwOLUeDoUcUE4lleZeORV+FIbJWX0cR2383GOGumTjM0XcJsD5pL'
                    . 'dlfHC9gAXCNJrJyFCPjrUnNXgshyAyfuc2VbpBrvwxzgGOG+1VuFJIzDDgbG8WGvgw5s8O91bTr4Rwv'
                    . 'JeLzWheeRODA4UXnkO2hatUmckdEPq7mBRHKveZsTwZLjWm3gPhmzGf6BvuAHEns0xsTH7rUgmmLwUH'
                    . 'fbDAG2jelieO1aMUGr6oPyAMGAN3savLKMMSEdYSb6Glr8joiapKvAdaV0lTblkDJbw6pZWKY=',
            ],
        ];

        $this->assertTrue($data->has($expectedUuid));

        $document = $data->get($expectedUuid);
        foreach ($expectedData[$expectedUuid] as $key => $value) {
            $this->assertTrue($document->has($key));
            $this->assertSame($value, $document->get($key));
        }
    }

    public function testExtractUsingSampleWithZeroUuid(): void
    {
        $sample = $this->fileContentPath('sample-to-extract-metadata-zero-cfdi.html');
        $extractor = new MetadataExtractor();
        $list = $extractor->extract($sample);
        $this->assertCount(0, $list);
    }
}
