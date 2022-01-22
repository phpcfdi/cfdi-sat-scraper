<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions\Ciec;

use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use PhpCfdi\ImageCaptchaResolver\CaptchaImage;
use Throwable;

class CiecLoginException extends LoginException
{
    /** @var CiecSessionData */
    private $sessionData;

    /** @var array<string, string> */
    private $postedData;

    /** @var CaptchaImage|null */
    private $captchaImage;

    /**
     * LoginException constructor.
     *
     * @param string $message
     * @param CiecSessionData $sessionData
     * @param string $contents
     * @param array<string, string> $postedData
     * @param Throwable|null $previous
     */
    public function __construct(string $message, CiecSessionData $sessionData, string $contents, array $postedData = [], Throwable $previous = null)
    {
        parent::__construct($message, $contents, $previous);
        $this->sessionData = $sessionData;
        $this->postedData = $postedData;
    }

    public static function notRegisteredAfterLogin(CiecSessionData $data, string $contents): self
    {
        $message = "It was expected to have the session registered on portal home page with RFC {$data->getRfc()}";
        return new self($message, $data, $contents);
    }

    public static function noCaptchaImageFound(CiecSessionData $data, string $contents, Throwable $previous = null): self
    {
        return new self('It was unable to find the captcha image', $data, $contents, [], $previous);
    }

    public static function captchaWithoutAnswer(CiecSessionData $data, CaptchaImage $captchaImage, Throwable $previous = null): self
    {
        $exception = new self('Unable to decode captcha', $data, '', [], $previous);
        $exception->captchaImage = $captchaImage;
        return $exception;
    }

    /**
     * @param CiecSessionData $data
     * @param string $contents
     * @param array<string, string> $postedData
     * @return self
     */
    public static function incorrectLoginData(CiecSessionData $data, string $contents, array $postedData): self
    {
        return new self('Unable to decode captcha', $data, $contents, $postedData);
    }

    public static function connectionException(string $when, CiecSessionData $data, SatHttpGatewayException $exception): self
    {
        return new self("Connection error when $when", $data, '', [], $exception);
    }

    public function getSessionData(): CiecSessionData
    {
        return $this->sessionData;
    }

    /** @return array<string, string> */
    public function getPostedData(): array
    {
        return $this->postedData;
    }

    public function getCaptchaImage(): ?CaptchaImage
    {
        return $this->captchaImage;
    }
}
