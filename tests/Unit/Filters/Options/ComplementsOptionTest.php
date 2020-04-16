<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Filters\Options;

use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Filters\Options\ComplementsOption;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class ComplementsOptionTest extends TestCase
{
    public function testBasicUsage(): void
    {
        $any = ComplementsOption::todos();
        $this->assertTrue($any->isTodos());
        // properties
        $this->assertSame('-1', $any->getInput());
        $this->assertSame('Cualquier complemento', $any->getDescription());
        // MicroCatalog asserts
        $this->assertSame('todos', $any->getEntryIndex());
        $this->assertSame('todos', $any->getEntryId());
        // Contracts\FilterOption::value assert
        $this->assertSame($any->getInput(), $any->value());
    }

    public function testCreateUsingInvalidValueThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ComplementsOption');
        new ComplementsOption('foo-bar');
    }
}
