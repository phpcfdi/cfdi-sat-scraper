<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use PhpCfdi\CfdiSatScraper\Internal\ResourceFileNamerByType;
use PhpCfdi\CfdiSatScraper\ResourceType;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class ResourceFileNamerByTypeTest extends TestCase
{
    public function testNameForByResourceTypeXml(): void
    {
        $type = ResourceType::xml();
        $uuid = 'b4fd0e43-4145-413b-a0fb-f6ce9b617e3c';
        $namer = new ResourceFileNamerByType($type);
        $this->assertSame("$uuid.xml", $namer->nameFor($uuid));
    }

    public function testNameForByResourceTypePdf(): void
    {
        $type = ResourceType::pdf();
        $uuid = 'b4fd0e43-4145-413b-a0fb-f6ce9b617e3c';
        $namer = new ResourceFileNamerByType($type);
        $this->assertSame("$uuid.pdf", $namer->nameFor($uuid));
    }

    public function testNameForByResourceTypeCancelRequest(): void
    {
        $type = ResourceType::cancelRequest();
        $uuid = 'b4fd0e43-4145-413b-a0fb-f6ce9b617e3c';
        $namer = new ResourceFileNamerByType($type);
        $this->assertSame("$uuid-cancel-request.pdf", $namer->nameFor($uuid));
    }

    public function testNameForByResourceTypeCancelVoucher(): void
    {
        $type = ResourceType::cancelVoucher();
        $uuid = 'b4fd0e43-4145-413b-a0fb-f6ce9b617e3c';
        $namer = new ResourceFileNamerByType($type);
        $this->assertSame("$uuid-cancel-voucher.pdf", $namer->nameFor($uuid));
    }
}
