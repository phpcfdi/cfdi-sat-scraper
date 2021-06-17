<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Captcha\Resolvers;

use PhpCfdi\CfdiSatScraper\Captcha\Resolvers\AntiCaptchaTinyClient\AntiCaptchaTinyClient;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use RuntimeException;

class AntiCaptchaResolver implements CaptchaResolverInterface
{
    /** @var AntiCaptchaTinyClient */
    private $antiCaptcha;

    /** @var int */
    private $timeout;

    public function __construct(AntiCaptchaTinyClient $antiCaptcha, int $timeout = 30)
    {
        $this->antiCaptcha = $antiCaptcha;
        $this->timeout = max(1, $timeout);
    }

    /**
     * @inheritDoc
     * @throws RuntimeException
     */
    public function decode(string $base64Image): string
    {
        $timeout = $this->timeout;
        $maxTime = $this->time() + $timeout;

        $taskId = $this->antiCaptcha->createTask($base64Image);
        $this->wait(3);

        while ($this->time() < $maxTime) {
            $result = $this->antiCaptcha->getTaskResult($taskId);
            if ('' !== $result) {
                return $result;
            }
            $this->wait(1);
        }

        throw new RuntimeException("Unable to get anti-captcha response for taskId $taskId after $timeout seconds");
    }

    public function time(): int
    {
        return time();
    }

    public function wait(int $seconds): void
    {
        sleep($seconds);
    }
}
