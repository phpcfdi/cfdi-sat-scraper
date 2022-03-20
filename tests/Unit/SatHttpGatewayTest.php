<?php

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PHPUnit\Framework\TestCase;

final class SatHttpGatewayTest extends TestCase
{
    public function testMethodPostLoginDataIsDeprecated(): void
    {
        $gateway = new SatHttpGateway();
        $this->expectDeprecation();
        $gateway->postLoginData('foo', []);
    }
}
