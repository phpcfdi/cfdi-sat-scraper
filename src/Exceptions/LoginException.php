<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use PhpCfdi\CfdiSatScraper\SatSessionData;
use RuntimeException;
use Throwable;

/**
 * The LoginException defines a problem on registering to the SAT platform with specific credentials.
 * It contains the SAT session data, retrieved contents and posted data.
 */
class LoginException extends RuntimeException implements SatException
{
    /** @var SatSessionData */
    private $sessionData;

    /** @var string */
    private $contents;

    /** @var array<string, string> */
    private $postedData;

    /**
     * LoginException constructor.
     *
     * @param string $message
     * @param SatSessionData $sessionData
     * @param string $contents
     * @param array<string, mixed> $post
     * @param Throwable|null $previous
     */
    public function __construct(string $message, SatSessionData $sessionData, string $contents, array $post = [], Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->sessionData = $sessionData;
        $this->contents = $contents;
        $this->postedData = $post;
    }

    public static function notRegisteredAfterLogin(SatSessionData $data, string $contents): self
    {
        $message = "It was expected to have the session registered on portal home page with RFC {$data->getRfc()}";
        return new self($message, $data, $contents);
    }

    public static function noCaptchaImageFound(SatSessionData $data, string $contents): self
    {
        return new self('It was unable to find the captcha image', $data, $contents);
    }

    public static function captchaWithoutAnswer(SatSessionData $data, string $imageBase64, Throwable $previous = null): self
    {
        return new self('Unable to decode captcha', $data, '', ['image' => $imageBase64], $previous);
    }

    /**
     * @param SatSessionData $data
     * @param string $contents
     * @param array<string, string> $postedData
     * @return self
     */
    public static function incorrectLoginData(SatSessionData $data, string $contents, array $postedData): self
    {
        return new self('Unable to decode captcha', $data, $contents, $postedData);
    }

    public static function connectionException(string $when, SatSessionData $data, SatHttpGatewayException $exception): self
    {
        return new self("Connection error when $when", $data, '', [], $exception);
    }

    public function getSessionData(): SatSessionData
    {
        return $this->sessionData;
    }

    public function getContents(): string
    {
        return $this->contents;
    }

    /** @return array<string, string> */
    public function getPostedData(): array
    {
        return $this->postedData;
    }
}
