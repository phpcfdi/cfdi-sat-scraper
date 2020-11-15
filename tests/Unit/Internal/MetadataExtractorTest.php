<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use PhpCfdi\CfdiSatScraper\Internal\MetadataExtractor;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use Symfony\Component\DomCrawler\Crawler;

final class MetadataExtractorTest extends TestCase
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
        $this->assertEmpty($extractor->obtainUrlXml($row));
    }

    public function testObtainUrls(): void
    {
        $urlXml = 'https://portalcfdi.facturaelectronica.sat.gob.mx/RecuperaCfdi.aspx'
            . '?Datos=M1peaCnvSPWnQLHcYL1G+TfX+fycHbwuKHo7GloSoS6fqnGuUFQ9RSqJcwdD4F5kspeWgLtl'
            . '/vgh+6fWBSRdELCsFI/nXD8HCOfiBTzcb0iW9LMYb3Se0U+ftfc6WC8xKL3ikJOv4JS5YVwJEdUvGup'
            . '1HJedFqaFw7EhVDA3Fxr/Jt/RUKRldWR9pQXtzJmNNNAvNuuQ1WbbhtvZIjJw28l01rtr34ZqjKKWQB'
            . 'zCDqWNVYFLmllZb1kLWWn9MtAkh/RqjfgaHuLlhhk8UTPQAjjyOrZ56ePLhIWK2ujfGbLORXeKe4dsu'
            . 'ykG3oF7Fxr/YjCpA9dGyuRJxYkVwwOLUeDoUcUE4lleZeORV+FIbJWX0cR2383GOGumTjM0XcJsD5pL'
            . 'dlfHC9gAXCNJrJyFCPjrUnNXgshyAyfuc2VbpBrvwxzgGOG+1VuFJIzDDgbG8WGvgw5s8O91bTr4Rwv'
            . 'JeLzWheeRODA4UXnkO2hatUmckdEPq7mBRHKveZsTwZLjWm3gPhmzGf6BvuAHEns0xsTH7rUgmmLwUH'
            . 'fbDAG2jelieO1aMUGr6oPyAMGAN3savLKMMSEdYSb6Glr8joiapKvAdaV0lTblkDJbw6pZWKY=';
        $urlPdf = 'https://portalcfdi.facturaelectronica.sat.gob.mx/RepresentacionImpresa.aspx?Datos='
            . 'ckC1j1jyuAA7FvuHrPbmWdDyv3G5UNPVexVxQEf7A77aTR/+k9TaZYH2gJML+zDUq//7UUofGbiltv1pBgruBT'
            . 'Jtz9RknxNl3ucvYTTgd7yHJYcoo51lbURfV8r/0SCo6NXoyatY/GVVTg3yXfcLX8ZjVFacQcXq0xwhhjaIpsB1'
            . 'Y+0YNcSuOR3Zz2dLXuGuQ/CLYBgRSwVUxn6NFE0hMDrBVwvcqhENnEdU1n5wzwWImIH3q5c95QHbEYnqYy6fjy'
            . 'edBFtR4nI3twnwdNmUjfbvejpkHOxT5u6tnELDonng1I36TFwAQqBFuOnPqSdJkzexkPhqVWCjzAG2v7hwSrrd'
            . '+xofJdFOe6tRNely2NFWOw79U0u6F5MGdjDgauBeT4SlJIFUOhYk7cFk6drY1hgmsPaEn4DArzCYIvXbTjsnA+'
            . 'eG76AGZt4HGu+CfDbAZOkbsuoPgTAwB+CkqfwjRgrdhC7LbkGsgKUh4tfkx2+42lfolYjUrp/fF8QPR1bGno0D'
            . 'eEvatg1s3OX57nZakGjE/8bVBrLE+FzxYYJFLBoHTx6CyIN4amjXlkemD7Makzjn7JCXuWP9dyyMoXBOa3+fND'
            . '1kV1iV2k2KWFAErpA=';
        $urlCancellationVoucher = 'https://portalcfdi.facturaelectronica.sat.gob.mx/'
            . 'AcuseSolicitudCancelacion.aspx?Datos=BORk2H7+XxIT7Av3LU+j3UNfE+IbROQabYYZ6ziVdU9/3aA9Yq'
            . '7IfVWHqtTGf3RFKFwFXFybIW482EYIH2uE0G6RmRcO/E932xvju96EZc8ZoaczA7/3wK4xkNSl2QfQ3qFP5ZDu+'
            . 'f/PwKZqcd3FfDygDTYxz6xrlw0++eT7lyscpRrjxel9GU3yOz9BWag/AGsjSRd1ZF5MmdejRSwiH5Iu0XCSqM23'
            . '68ClXXKiSaIW+skP6jRadhzHnskv13UEc+g2+5o1V3j4Hwkcr3SE0vhR28sBO3VoeccxpvgHkFBmsdkmST8E3Ne'
            . 'GNjyUrF5etAH3VU9Rkg1WaDjd14ZQePH35V7QnYNKzWrXfkrR+y5KQ4dOpcAFtsYQStgT78EPJsJlqK1bz+LQ5Y'
            . 'csjFQRuGHco/GZILo8efugERislOmbNPsy7AehaAa8Vd4uE5n5Sld3C2gRJPzh1YRUE6gpgITEN4dNAdoV9iBVi'
            . '6pEdH08lMwfXMgbC/9Cnnv35upJ2HYlKX2qu03VRPK7XYmulnpYYXOLfNbaa48mUpbZIVDmxMgIx2IEioPvXSMn'
            . '4X0oinFHw+ACzo4vpxzsz8jtJoJE5m4dejqc0f8QKthwuqTK+H8FjYGUSEO8RsmZcIHGJNw1ERIIIpa4EmJYcB6'
            . 'yVaqVCZq+mTeoc0EtCtiJJZ9hs2i/hKcqlbGh2FfVVQejDuPBGecsTQKfVd1G6nKB6g==';

        $sample = $this->fileContentPath('sample-to-extract-metadata-one-cfdi.html');
        $extractor = new MetadataExtractor();
        $list = $extractor->extract($sample);
        $metadataCurrent = $list->get('b97262e5-704c-4bf7-ae26-9174fef04d63');
        $metadataCancelled = $list->get('1271B699-21D3-4569-9CD7-F22BD99ED395');

        $this->assertSame(
            $urlXml,
            $metadataCurrent->get('urlXml'),
            'The URL to download the CFDI XML was not found as expected'
        );
        $this->assertSame(
            $urlPdf,
            $metadataCurrent->get('urlPdf'),
            'The URL to download the CFDI PDF was not found as expected'
        );
        $this->assertSame(
            $urlCancellationVoucher,
            $metadataCancelled->get('urlCancellationVoucher'),
            'The URL to download the CFDI cancellation voucher was not found as expected'
        );
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
        $this->assertGreaterThanOrEqual(1, count($data));

        $expectedUuid = 'b97262e5-704c-4bf7-ae26-9174fef04d63';

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

    public function testExtractOmmitsRecordsWithMissingUuid(): void
    {
        $sample = $this->fileContentPath('fake-to-extract-metadata-missing-uuid.html');
        $extractor = new MetadataExtractor();
        $list = $extractor->extract($sample);
        $this->assertCount(2, $list);
        $this->assertTrue($list->has('B97262E5-704C-4BF7-AE26-000000000001'));
        $this->assertTrue($list->has('B97262E5-704C-4BF7-AE26-000000000002'));
    }

    public function testExtractUsingContentWithoutData(): void
    {
        $extractor = new MetadataExtractor();
        $list = $extractor->extract('');
        $this->assertCount(0, $list);
    }
}
