<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions\Ciec;

use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;

/**
 * This immutable class is the store of the data required to log in into SAT
 */
class CiecSessionData
{
    public const DEFAULT_MAX_TRIES_CAPTCHA = 3;

    public const DEFAULT_MAX_TRIES_LOGIN = 3;

    private string $rfc;

    private string $ciec;

    private int $maxTriesCaptcha;

    private int $maxTriesLogin;

    /**
     * @param int $maxTriesCaptcha if lower than 1 is set to 1
     * @param int $maxTriesLogin if lower than 1 is set to 1
     *
     * @throws InvalidArgumentException when RFC is an empty string
     * @throws InvalidArgumentException when CIEC is an empty string
     */
    public function __construct(
        string $rfc,
        string $ciec,
        private CaptchaResolverInterface $captchaResolver,
        int $maxTriesCaptcha = self::DEFAULT_MAX_TRIES_CAPTCHA,
        int $maxTriesLogin = self::DEFAULT_MAX_TRIES_LOGIN
    ) {
        if ('' === $rfc) {
            throw InvalidArgumentException::emptyInput('RFC');
        }
        if ('' === $ciec) {
            throw InvalidArgumentException::emptyInput('CIEC');
        }
        $this->rfc = $rfc;
        $this->ciec = $ciec;
        $this->maxTriesCaptcha = max(1, $maxTriesCaptcha);
        $this->maxTriesLogin = max(1, $maxTriesLogin);
    }

    public function getRfc(): string
    {
        return $this->rfc;
    }

    public function getCiec(): string
    {
        return $this->ciec;
    }

    public function getCaptchaResolver(): CaptchaResolverInterface
    {
        return $this->captchaResolver;
    }

    public function getMaxTriesCaptcha(): int
    {
        return $this->maxTriesCaptcha;
    }

    public function getMaxTriesLogin(): int
    {
        return $this->maxTriesLogin;
    }
}
