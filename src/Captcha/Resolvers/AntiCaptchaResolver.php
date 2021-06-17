<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Captcha\Resolvers;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use PhpCfdi\CfdiSatScraper\Captcha\Resolvers\AntiCaptchaTinyClient\AntiCaptchaTinyClient;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use RuntimeException;

class AntiCaptchaResolver implements CaptchaResolverInterface
{
    public const DEFAULT_TIMEOUT = 30;

    /** @var AntiCaptchaTinyClient */
    private $antiCaptcha;

    /** @var int */
    private $timeout;

    public function __construct(AntiCaptchaTinyClient $antiCaptcha, int $timeout)
    {
        $this->antiCaptcha = $antiCaptcha;
        $this->timeout = $timeout;
    }

    /**
     * Factory method with defaults
     *
     * @param string $clientKey
     * @param ClientInterface|null $httpClient
     * @param int $timeout
     * @return self
     */
    public static function create(
        string $clientKey,
        ClientInterface $httpClient = null,
        int $timeout = self::DEFAULT_TIMEOUT
    ): self {
        $httpClient = $httpClient ?? new Client();
        return new self(new AntiCaptchaTinyClient($httpClient, $clientKey), $timeout);
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getAntiCaptchaClient(): AntiCaptchaTinyClient
    {
        return $this->antiCaptcha;
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
