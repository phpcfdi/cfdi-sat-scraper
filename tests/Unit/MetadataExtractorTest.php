<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\MetadataExtractor;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

class MetadataExtractorTest extends TestCase
{
    public function testUsingFakeInputWithZeroUuids(): void
    {
        $sample = $this->fileContentPath('fake-to-extract-metadata-zero-cfdi.html');
        $extractor = new MetadataExtractor();
        $this->assertSame(0, $extractor->extract($sample));
        $this->assertSame([], $extractor->getData());
    }

    public function testUsingFakeInput(): void
    {
        $sample = $this->fileContentPath('fake-to-extract-metadata-one-cfdi.html');
        $extractor = new MetadataExtractor();
        $this->assertSame(1, $extractor->extract($sample));
        $expectedUuid = 'B97262E5-704C-4BF7-AE26-9174FEF04D63';
        $this->assertArrayHasKey($expectedUuid, $extractor->getData());
    }

    public function testExtractUsingSampleWithOneUuid(): void
    {
        // this sample file contains 1 UUID only
        $sample = $this->fileContentPath('sample-to-extract-metadata.html');

        $extractor = new MetadataExtractor();
        $this->assertSame(1, $extractor->extract($sample));

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
                'fechaCancelacion' => '',
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

        $data = $extractor->getData();
        $this->assertArrayHasKey($expectedUuid, $data);

        $document = $data[$expectedUuid];
        foreach ($expectedData[$expectedUuid] as $key => $value) {
            $this->assertArrayHasKey($key, $document);
            $this->assertSame($value, $document[$key]);
        }
    }

    public function testExtractUsingSampleWithZeroUuid(): void
    {
        $sample = $this->fileContentPath('sample-to-extract-metadata-zero-cfdi.html');
        $extractor = new MetadataExtractor();
        $this->assertSame(0, $extractor->extract($sample));
        $this->assertSame([], $extractor->getData());
    }
}
