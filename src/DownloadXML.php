<?php

namespace PhpCfdi\CfdiSatScraper;

use Closure;
use GuzzleHttp\Promise\EachPromise;
use Psr\Http\Message\ResponseInterface;

/**
 * Class DownloadXML.
 */
class DownloadXML
{
    /**
     * @var SATScraper
     */
    protected $satScraper;

    /**
     * @var int
     */
    protected $concurrency;

    /**
     * DownloadXML constructor.
     */
    public function __construct()
    {
        $this->concurrency = 10;
    }

    /**
     * @param callable $callback
     */
    public function download(callable $callback)
    {
        $promises = $this->makePromises();

        (new EachPromise($promises, [
            'concurrency' => $this->concurrency,
            'fulfilled' => function (ResponseInterface $response) use ($callback) {
                $callback($response->getBody(), $this->getFileName($response));
            },
        ]))->promise()
            ->wait();
    }

    /**
     * @return \Generator
     */
    protected function makePromises()
    {
        foreach ($this->satScraper->getUrls() as $link) {
            yield $this->satScraper->getClient()->requestAsync('GET', $link, [
                'future' => true,
                'verify' => false,
                'cookies' => $this->satScraper->getCookie(),
            ]);
        }
    }

    /**
     * @param ResponseInterface $response
     *
     * @return string
     */
    protected function getFileName(ResponseInterface $response)
    {
        $contentDisposition = $response->getHeaderLine('content-disposition');
        $partsOfContentDisposition = explode(';', $contentDisposition);
        $fileName = str_replace('filename=', '', isset($partsOfContentDisposition[1]) ? $partsOfContentDisposition[1] : '');

        return !empty($fileName) ? $fileName : uniqid() . '.xml';
    }

    /**
     * @param SATScraper $satScraper
     *
     * @return DownloadXML
     */
    public function setSatScraper(SATScraper $satScraper)
    {
        $this->satScraper = $satScraper;

        return $this;
    }

    /**
     * @param int $concurrency
     *
     * @return DownloadXML
     */
    public function setConcurrency(int $concurrency)
    {
        $this->concurrency = $concurrency;

        return $this;
    }

    /**
     * @param string $path
     * @param bool $createDir
     * @param int $mode
     */
    public function saveTo(string $path, bool $createDir, $mode = 0775)
    {
        if (!$createDir && !file_exists($path)) {
            throw new \InvalidArgumentException("The provider path [{$path}] not exists");
        }

        if ($createDir && !file_exists($path)) {
            mkdir($path, $mode);
        }

        $this->download(function ($content, $name) use ($path) {
            $f = new \SplFileObject($path . DIRECTORY_SEPARATOR . $name, 'w');
            $f->fwrite($content);
            $f = null;
        });
    }
}
