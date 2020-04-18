<?php
/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use ArrayObject;
use DateTimeImmutable;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @extends ArrayObject<int, array>
 */
class HttpLogger extends ArrayObject
{
    /** @var string */
    private $destinationDir;

    public function __construct(string $destinationDir)
    {
        parent::__construct();
        // if is not empty and is not an absolute path, prepend project dir
        if ('' !== $destinationDir && ! in_array(substr($destinationDir, 0, 1), ['/', '\\'], true)) {
            $destinationDir = dirname(__DIR__, 3) . '/' . $destinationDir;
        }
        $this->destinationDir = $destinationDir;
    }

    /**
     * @param mixed $entry
     */
    public function append($entry): void
    {
        $this->write($entry);
        parent::append($entry);
    }

    /**
     * @param int|string|null $index
     * @param mixed $entry
     */
    public function offsetSet($index, $entry): void
    {
        if (null === $index) {
            $this->write($entry);
        }
        parent::offsetSet($index, $entry);
    }

    /**
     * @param mixed $entry
     */
    public function write($entry): void
    {
        if (! is_array($entry)) {
            return;
        }
        if ('' === $this->destinationDir) {
            return;
        }
        if (! file_exists($this->destinationDir)) {
            mkdir($this->destinationDir, 0755, true);
        }
        /** @var RequestInterface $request */
        $request = $entry['request'];
        /** @var ResponseInterface $response */
        $response = $entry['response'];
        $time = new DateTimeImmutable();
        $file = sprintf(
            '%s/%s.%06d-%s-%s.json',
            $this->destinationDir,
            $time->format('c'),
            $time->format('u'),
            strtolower($request->getMethod()),
            $this->slugify((string) sprintf('%s%s', $request->getUri()->getHost(), $request->getUri()->getPath()))
        );
        file_put_contents($file, $this->entryToJson($request, $response), FILE_APPEND);
    }

    public function slugify(string $text): string
    {
        $text = (string) preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = (string) iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = (string) preg_replace('~[^\w\-]+~', '', $text);
        $text = trim($text, '-');
        $text = (string) preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return $text;
    }

    public function entryToJson(RequestInterface $request, ResponseInterface $response): string
    {
        $json = json_encode(
            [
                    'uri' => sprintf('%s: %s', $request->getMethod(), (string)$request->getUri()),
                    'request' => [
                        'headers' => $request->getHeaders(),
                        'body' => $this->bodyToVars((string)$request->getBody()),
                    ],
                    'response' => [
                        'headers' => $response->getHeaders(),
                        'body' => $this->bodyToVars((string)$request->getBody()),
                    ],
                ],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS
        ) . PHP_EOL;
        $request->getBody()->rewind();
        $response->getBody()->rewind();
        return $json;
    }

    public function bodyToVars(string $body): array
    {
        $variables = [];
        parse_str($body, $variables);
        return $variables;
    }
}
