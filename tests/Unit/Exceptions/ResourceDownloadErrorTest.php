<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Exceptions;

use PhpCfdi\CfdiSatScraper\Exceptions\ResourceDownloadError;
use PhpCfdi\CfdiSatScraper\Exceptions\SatException;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use RuntimeException;
use Throwable;

final class ResourceDownloadErrorTest extends TestCase
{
    public function testConstructor(): void
    {
        $message = 'message';
        $uuid = 'uuid';
        $reason = 'reason';
        $previous = $this->createMock(Throwable::class);

        $exception = new ResourceDownloadError($message, $uuid, $reason, $previous);

        $this->assertInstanceOf(RuntimeException::class, $exception);
        $this->assertInstanceOf(SatException::class, $exception);
        $this->assertSame($message, $exception->getMessage());
        $this->assertSame(0, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
        $this->assertSame($uuid, $exception->getUuid());
        $this->assertSame($reason, $exception->getReason());
    }

    public function testConstructorWithThrowableReasonWithoutPrevious(): void
    {
        $message = 'message';
        $uuid = 'uuid';
        $reason = $this->createMock(Throwable::class);

        $exception = new ResourceDownloadError($message, $uuid, $reason);

        $this->assertSame($reason, $exception->getPrevious());
        $this->assertSame($reason, $exception->getReason());
    }

    public function testOnRejected(): void
    {
        $uuid = 'uuid';
        $reason = 'something';
        $exception = ResourceDownloadError::onRejected($uuid, $reason);
        $this->assertSame("Download of UUID uuid was rejected, reason: $reason", $exception->getMessage());
        $this->assertSame($uuid, $exception->getUuid());
        $this->assertSame($reason, $exception->getReason());
    }

    /** @return array<string, array{string, mixed}> */
    public function providerReasonToString(): array
    {
        $stringable = new class () {
            public function __toString(): string
            {
                return 'fixed response';
            }
        };
        $other = (object) ['foo' => 'bar'];
        return [
            'string' => ['string', 'string'],
            'scalar' => ['123', 123],
            'throwable' => ['RuntimeException: Message', new RuntimeException('Message')],
            'stringable' => ['fixed response', $stringable],
            'other' => [print_r($other, true), $other],
        ];
    }

    /**
     * @param string $expected
     * @param mixed $reason
     * @dataProvider providerReasonToString
     */
    public function testReasonToString(string $expected, $reason): void
    {
        $value = ResourceDownloadError::reasonToString($reason);
        $this->assertSame($expected, $value);
    }
}
