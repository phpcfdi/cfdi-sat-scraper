<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Filters\Options\UuidOption;
use PhpCfdi\CfdiSatScraper\QueryByUuid;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class QueryByUuidTest extends TestCase
{
    public function testQueryByUuidPropertyUuid(): void
    {
        $initialUuid = new UuidOption('df71f5a5-134c-4c94-ab87-0476436a9a5e');
        $query = new QueryByUuid($initialUuid);
        $this->assertSame($initialUuid, $query->getUuid());

        $changedUuid = new UuidOption('5062dbee-2ccc-48a9-98cf-550c9aeb7466');
        $returnAfterSet = $query->setUuid($changedUuid);
        $this->assertSame($changedUuid, $query->getUuid());
        $this->assertSame($query, $returnAfterSet);
    }

    public function testQueryByUuidConstructWithEmptyUuidThrowsException(): void
    {
        $emptyUuid = new UuidOption('');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UUID');

        new QueryByUuid($emptyUuid);
    }

    public function testQueryByUuidSetEmptyUuidThrowsException(): void
    {
        $initialUuid = new UuidOption('df71f5a5-134c-4c94-ab87-0476436a9a5e');
        $query = new QueryByUuid($initialUuid);
        $emptyUuid = new UuidOption('');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('UUID');

        $query->setUuid($emptyUuid);
    }
}
