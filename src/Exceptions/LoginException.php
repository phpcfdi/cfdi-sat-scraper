<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use RuntimeException;
use Throwable;

class LoginException extends RuntimeException implements SatException
{
    /** @var array<string, mixed> */
    private $context;

    /**
     * LoginException constructor.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @param Throwable|null $previous
     */
    public function __construct(string $message, array $context, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->context = $context;
    }

    public static function notRegisteredAfterLogin(string $rfc, string $htmlContents): self
    {
        return new self("It was expected to have the session registered on portal home page with RFC $rfc", [
            'rfc' => $rfc,
            'contents' => $htmlContents,
        ]);
    }

    public static function noCaptchaImageFound(string $url, string $htmlContents): self
    {
        return new self("It was unable to find the captcha image from url $url", [
            'url' => $url,
            'contents' => $htmlContents,
        ]);
    }

    public static function captchaWithoutAnswer(string $imageBase64, CaptchaResolverInterface $resolver): self
    {
        return new self('Unable to decode captcha', [
            'imageBase64' => $imageBase64,
            'contents' => $resolver,
        ]);
    }

    /**
     * @param array<string, string> $postData
     * @return self
     */
    public static function incorrectLoginData(array $postData): self
    {
        return new self('Unable to decode captcha', $postData);
    }
}
