<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Internal;

use PhpCfdi\CfdiSatScraper\Internal\MetaRefreshInspector;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class MetaRefreshInspectorTest extends TestCase
{
    public function testObtainMetaRefreshAbsolute(): void
    {
        $html = <<< HTML
            <html>
                <head>
                    <meta http-equiv="refresh" content="0; url=https://example.com/?foo=bar">
                </head>
            </html>
            HTML;

        $inspector = new MetaRefreshInspector();
        $url = $inspector->obtainUrl($html, '');

        $this->assertSame('https://example.com/?foo=bar', $url);
    }

    public function testObtainMetaRefreshRelativeToPath(): void
    {
        $html = <<< HTML
            <html>
                <head>
                    <meta http-equiv="refresh" content="0; url=redirect.php?destination=1">
                </head>
            </html>
            HTML;

        $inspector = new MetaRefreshInspector();
        $url = $inspector->obtainUrl($html, 'https://example.com/foo/bar/');

        $this->assertSame('https://example.com/foo/bar/redirect.php?destination=1', $url);
    }

    public function testObtainMetaRefreshRelativeToServer(): void
    {
        $html = <<< HTML
            <html>
                <head>
                    <meta http-equiv="refresh" content="0; url=/redirect.php?destination=1">
                </head>
            </html>
            HTML;

        $inspector = new MetaRefreshInspector();
        $url = $inspector->obtainUrl($html, 'https://example.com/foo/bar/');

        $this->assertSame('https://example.com/redirect.php?destination=1', $url);
    }

    public function testObtainMetaRefreshWithoutElement(): void
    {
        $html = <<< HTML
            <html>
                <body>
                    Foo Bar
                </body>
            </html>
            HTML;

        $inspector = new MetaRefreshInspector();

        $this->assertEmpty($inspector->obtainUrl($html, ''));
    }
}
