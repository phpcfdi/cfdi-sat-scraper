<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use JsonSerializable;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Metadata;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class MetadataTest extends TestCase
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

    public function testCreatingWithEmptyUuidThrowsInvalidArgumentException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UUID');
        new Metadata('');
    }

    public function testResourceDownloadXml(): void
    {
        $faker = $this->fakes()->faker();
        $uuid = $faker->uuid;
        $metadata = new Metadata($uuid);
        $this->assertSame('', $metadata->get(ResourceType::xml()->value()));

        $url = $faker->url;
        $metadata = new Metadata($uuid, [ResourceType::xml()->value() => $url]);
        $this->assertSame($url, $metadata->get(ResourceType::xml()->value()));
    }

    public function testJsonSerializable(): void
    {
        $faker = $this->fakes()->faker();
        $uuid = $faker->uuid;
        $values = [
            ResourceType::xml()->value() => $faker->url, // this is expected
            'foo' => 'bar', // this is extra
        ];
        $metadata = new Metadata($uuid, $values);
        $this->assertInstanceOf(JsonSerializable::class, $metadata);
        $values['uuid'] = $uuid;
        $this->assertEquals($values, $metadata->jsonSerialize(), 'The jsonSerialize mthod did not return the expected data');
    }

    public function testCloningPreserveContents(): void
    {
        $base = new Metadata($this->fakes()->faker()->uuid, ['foo' => 'x-foo', 'bar' => 'x-bar']);
        $clon = clone $base;
        $this->assertEquals($base, $clon);
        $this->assertNotSame($base, $clon);
    }
}
