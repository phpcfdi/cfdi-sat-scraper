<?php

/**
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Captcha\Resolvers;

use PhpCfdi\CfdiSatScraper\Captcha\Resolvers\AntiCaptchaResolver;
use PhpCfdi\CfdiSatScraper\Captcha\Resolvers\AntiCaptchaTinyClient\AntiCaptchaTinyClient;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class AntiCaptchaResolverTest extends TestCase
{
    public function testDecode(): void
    {
        /*
         * This test probes that decode implementation will wait for 3 seconds
         * and then will get the response after query for solution 2 times
         */

        /** @var AntiCaptchaTinyClient&MockObject $client */
        $client = $this->createMock(AntiCaptchaTinyClient::class);
        $client->expects($this->once())->method('createTask')->willReturn('task-id');
        $client->expects($this->atMost(2))->method('getTaskResult')->willReturn('', 'solution');

        $resolver = new class($client, 5) extends AntiCaptchaResolver {
            /** @var int */
            private $time;

            public function __construct(AntiCaptchaTinyClient $antiCaptcha, int $timeout = 30)
            {
                parent::__construct($antiCaptcha, $timeout);
                $this->time = time();
            }

            /** @noinspection PhpMissingParentCallCommonInspection */
            public function time(): int
            {
                return $this->time;
            }

            /** @noinspection PhpMissingParentCallCommonInspection */
            public function wait(int $seconds): void
            {
                $this->time = $this->time + $seconds;
            }
        };

        $code = $resolver->decode('IMAGEBASE64');
        $this->assertSame('solution', $code);
    }
}
