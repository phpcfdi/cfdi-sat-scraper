<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

use Stringable;
use Throwable;

/**
 * This exception (called intentionally error) encloses a problem with a reason
 * that was thrown when performing an XML download.
 *
 * There are specializations, that stores reason with a specific type.
 *
 * @see ResourceDownloadResponseError
 * @see ResourceDownloadRequestExceptionError
 */
class ResourceDownloadError extends \RuntimeException implements SatException
{
    private readonly mixed $reason;

    /**
     * ResourceDownloadError constructor.
     *
     * If the reason is a Throwable and previous was not defined, then it set up previous as reason.
     *
     * @param mixed $reason
     */
    public function __construct(string $message, private readonly string $uuid, $reason, ?Throwable $previous = null)
    {
        if (null === $previous && $reason instanceof Throwable) {
            $previous = $reason;
        }
        parent::__construct($message, 0, $previous);
        $this->reason = $reason;
    }

    public static function onRejected(string $uuid, mixed $reason): self
    {
        $message = sprintf('Download of UUID %s was rejected, reason: %s', $uuid, static::reasonToString($reason));
        return new self($message, $uuid, $reason);
    }

    public static function reasonToString(mixed $reason): string
    {
        if ($reason instanceof Throwable) {
            return $reason::class . ': ' . $reason->getMessage();
        }
        if (is_scalar($reason)) {
            return strval($reason);
        }
        if (is_object($reason) && is_callable([$reason, '__toString'])) {
            /**
             * Fix PHPStan false positive detecting cast from object to string
             * @phpstan-var Stringable $reason
             * @noinspection PhpMultipleClassDeclarationsInspection
             */
            return strval($reason);
        }
        return print_r($reason, true);
    }

    /**
     * The UUID related to the error
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * The given reason of the exception
     */
    public function getReason(): mixed
    {
        return $this->reason;
    }
}
