<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Sessions\Fiel;

use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielSessionData;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class FielSessionDataTest extends TestCase
{
    use CreateFakeFielTrait;

    public function testGetFiel(): void
    {
        $fiel = $this->createFakeFiel();
        $sessionData = new FielSessionData($fiel);
        $this->assertSame($fiel, $sessionData->getFiel());
    }

    public function testGetRfc(): void
    {
        $expected = 'EKU9003173C9';
        $fiel = $this->createFakeFiel();
        $sessionData = new FielSessionData($fiel);
        $this->assertSame($expected, $sessionData->getRfc());
    }

    public function testGetValidTo(): void
    {
        $expected = '230613210515Z';
        $fiel = $this->createFakeFiel();
        $sessionData = new FielSessionData($fiel);
        $this->assertSame($expected, $sessionData->getValidTo());
    }

    public function testGetSerialNumber(): void
    {
        $expected = '30001000000400002417';
        $fiel = $this->createFakeFiel();
        $sessionData = new FielSessionData($fiel);
        $this->assertSame($expected, $sessionData->getSerialNumber());
    }

    public function testSign(): void
    {
        $textToSign = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit...';
        $expected = ''
            . 'H2+wV4IzctmRYeHiPidSUCeYxVecDiyAVLhENpggAJDY62C+p1oSJw1Rife/+D58WQICI5z5/ewQH9Js'
            . 'vsdeVh+dx6MCnS9VWF4FxxBCSngJMVTU3iIPM8th+Qix3LNSzzi0iRfy6s4wIepN5mi0Bdq9UejgDO72'
            . 'PddyLqldbAVVNTIrYmBSuWLrt0x/xHurRZGnY+TK5n2ytIYVeckupP/56Gsg6IHZXxGb3wUBu7n9d+a6'
            . 'sn2Z7N/ZsnTNJbibR9CSQObDdtMmXFPMmCs6f43NvwUoWpejVTTqa1dHsZXVHmejCIyY3D1e9kOMXfyP'
            . 'ddtViHXrg9lKgXCLV39p7A==';
        $fiel = $this->createFakeFiel();
        $sessionData = new FielSessionData($fiel);
        $this->assertSame(base64_decode($expected), $sessionData->sign($textToSign, OPENSSL_ALGO_SHA256));
    }
}
