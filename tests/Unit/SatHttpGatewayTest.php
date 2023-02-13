<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PHPUnit\Framework\TestCase;

final class SatHttpGatewayTest extends TestCase
{
    public function testMethodPostLoginDataIsDeprecated(): void
    {
        $gateway = new class () extends SatHttpGateway {
            public function postCiecLoginData(string $loginUrl, array $formParams): string
            {
                return '';
            }
        };

        @$gateway->postLoginData('foo', []);
        $error = error_get_last() ?? [];
        $this->assertSame(E_USER_DEPRECATED, intval($error['type'] ?? 0));
    }
}
