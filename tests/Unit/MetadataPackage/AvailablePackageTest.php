<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\MetadataPackage;

use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\MetadataPackage\AvailablePackage;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class AvailablePackageTest extends TestCase
{
    public function testPackagePreservesTheUuidAsGiven(): void
    {
        $package = new AvailablePackage('7C8D02B2-91A6-4AE0-801D-F05D5027939C', 'blobUri');
        $this->assertSame('7C8D02B2-91A6-4AE0-801D-F05D5027939C', $package->getUuid());
        $this->assertSame('blobUri', $package->getDownloadUrl());
    }

    public function testPackageWithEmptyUuidThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AvailablePackage('', 'blobUri');
    }

    public function testPackageWithEmptyDownloadUrlThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new AvailablePackage('7C8D02B2-91A6-4AE0-801D-F05D5027939C', '');
    }

    public function testIsJsonSerializable(): void
    {
        $uuid = '7C8D02B2-91A6-4AE0-801D-F05D5027939C';
        $downloadUrl = 'blobUri';
        $expected = [
            'uuid' => $uuid,
            'downloadUrl' => $downloadUrl,
        ];
        $package = new AvailablePackage($uuid, $downloadUrl);

        $this->assertSame($expected, $package->jsonSerialize());
    }
}
