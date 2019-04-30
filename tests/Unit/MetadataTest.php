<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Metadata;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

class MetadataTest extends TestCase
{
    public function testCreateAndRetrieveData(): void
    {
        $uuid = $this->fakes()->faker()->uuid;
        $item = new Metadata($uuid, ['bar' => 'x-bar', 'foo' => 'x-foo']);
        $this->assertSame($uuid, $item->uuid());
        $this->assertSame('x-foo', $item->get('foo'));
        $this->assertSame('', $item->get('xee'), 'non existent data must return empty string');
    }

    public function testUuidPassedOnConstructorOverridesUuidOnData(): void
    {
        $uuid = $this->fakes()->faker()->uuid;
        $item = new Metadata($uuid, ['uuid' => 'x-foo']);
        $this->assertSame($uuid, $item->uuid());
        $this->assertSame($uuid, $item->get('uuid'));
    }

    public function testUuidPassedOnConstructorWorksAsAnyOtherData(): void
    {
        $uuid = $this->fakes()->faker()->uuid;
        $item = new Metadata($uuid);
        $this->assertSame($uuid, $item->get('uuid'));
        $this->assertTrue($item->has('uuid'));
    }
}
