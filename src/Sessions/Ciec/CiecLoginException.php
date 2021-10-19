<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions\Ciec;

use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Exceptions\SatHttpGatewayException;
use Throwable;

class CiecLoginException extends LoginException
{
    /** @var CiecSessionData */
    private $sessionData;

    /** @var array<string, string> */
    private $postedData;

    /**
     * LoginException constructor.
     *
     * @param string $message
     * @param CiecSessionData $sessionData
     * @param string $contents
     * @param array<string, mixed> $post
     * @param Throwable|null $previous
     */
    public function __construct(string $message, CiecSessionData $sessionData, string $contents, array $post = [], Throwable $previous = null)
    {
        parent::__construct($message, $contents, $previous);
        $this->sessionData = $sessionData;
        $this->postedData = $post;
    }

    public static function notRegisteredAfterLogin(CiecSessionData $data, string $contents): self
    {
        $message = "It was expected to have the session registered on portal home page with RFC {$data->getRfc()}";
        return new self($message, $data, $contents);
    }

    public static function noCaptchaImageFound(CiecSessionData $data, string $contents): self
    {
        return new self('It was unable to find the captcha image', $data, $contents);
    }

    public static function captchaWithoutAnswer(CiecSessionData $data, string $imageBase64, Throwable $previous = null): self
    {
        return new self('Unable to decode captcha', $data, '', ['image' => $imageBase64], $previous);
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
}
