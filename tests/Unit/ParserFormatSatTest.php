<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit;

use PhpCfdi\CfdiSatScraper\ParserFormatSAT;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class ParserFormatSatTest extends TestCase
{
    public function testWithEmptySource(): void
    {
        $parser = new ParserFormatSAT();
        $this->assertSame([], $parser->getFormValues(''));
    }

    public function testWithExactSource(): void
    {
        $data = [
            '__EVENTTARGET' => 'x-event-target',
            '__EVENTARGUMENT' => 'x-event-argument',
            '__LASTFOCUS' => 'x-last-focus',
            '__VIEWSTATE' => 'x-event-viewstate',
        ];
        $source = $this->buildFakeSourceData($data);
        $parser = new ParserFormatSAT();
        $this->assertSame($data, $parser->getFormValues($source));
    }

    public function testWithSourceMissingKeys(): void
    {
        $data = [
            '__EVENTTARGET' => 'x-event-target',
            '__EVENTARGUMENT' => 'x-event-argument',
            '__FOO_BAR' => 'foo-bar',
            '__VIEWSTATE' => 'x-event-viewstate',
        ];
        $source = $this->buildFakeSourceData($data);
        $parser = new ParserFormatSAT();
        unset($data['__FOO_BAR']);
        $this->assertSame($data, $parser->getFormValues($source));
    }

    public function testWithSourceOtherKeys(): void
    {
        $data = [
            '__EVENTTARGET' => 'x-event-target',
            '__EVENTARGUMENT' => 'x-event-argument',
            '__VIEWSTATE' => 'x-event-viewstate',
        ];
        $source = $this->buildFakeSourceData($data);
        $parser = new ParserFormatSAT();
        $this->assertSame($data, $parser->getFormValues($source));
    }

    public function buildFakeSourceData(array $values): string
    {
        return implode('', array_map(function ($fieldName, $fieldValue): string {
            return '|' . implode('|', [strlen($fieldValue), 'hiddenField', $fieldName, $fieldValue]);
        }, array_keys($values), $values));
    }
}
