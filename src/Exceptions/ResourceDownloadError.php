<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Exceptions;

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
    /** @var string */
    private $uuid;

    /** @var mixed */
    private $reason;

    /**
     * ResourceDownloadError constructor.
     *
     * If the reason is a Throwable and previous was not defined, then it set up previous as reason.
     *
     * @param string $message
     * @param string $uuid
     * @param mixed $reason
     * @param Throwable|null $previous
     */
    public function __construct(string $message, string $uuid, $reason, Throwable $previous = null)
    {
        if (null === $previous && $reason instanceof Throwable) {
            $previous = $reason;
        }
        parent::__construct($message, 0, $previous);
        $this->uuid = $uuid;
        $this->reason = $reason;
    }

    /**
     * @param string $uuid
     * @param mixed $reason
     * @return self
     */
    public static function onRejected(string $uuid, $reason): self
    {
        $message = sprintf('Download of UUID %s was rejected, reason: %s', $uuid, static::reasonToString($reason));
        return new self($message, $uuid, $reason);
    }

    /**
     * @param mixed $reason
     * @return string
     */
    public static function reasonToString($reason): string
    {
        if ($reason instanceof Throwable) {
            return get_class($reason) . ': ' . $reason->getMessage();
        }
        if (is_scalar($reason)) {
            return strval($reason);
        }
        if (is_object($reason) && is_callable([$reason, '__toString'])) {
            return strval($reason);
        }
        return print_r($reason, true);
    }

    /**
     * The UUID related to the error
     *
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * The given reason of the exception
     *
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }
}
