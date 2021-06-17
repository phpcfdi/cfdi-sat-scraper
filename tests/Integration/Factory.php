<?php
/**
 * @noinspection PhpUnhandledExceptionInspection
 */

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use PhpCfdi\CfdiSatScraper\Captcha\Resolvers\AntiCaptchaResolver;
use PhpCfdi\CfdiSatScraper\Captcha\Resolvers\ConsoleCaptchaResolver;
use PhpCfdi\CfdiSatScraper\Captcha\Resolvers\DeCaptcherCaptchaResolver;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\SatSessionData;
use PhpCfdi\CfdiSatScraper\Tests\CaptchaLocalResolver\CaptchaLocalResolver;
use PhpCfdi\CfdiSatScraper\Tests\CaptchaLocalResolver\CaptchaLocalResolverClient;
use RuntimeException;

class Factory
{
    /** @var string */
    private $repositoryPath;

    /** @var SatScraper|null */
    private $scraper;

    /** @var Repository|null */
    private $repository;

    public function __construct(string $repositoryPath)
    {
        $this->repositoryPath = $repositoryPath;
    }

    public static function createCaptchaResolver(): CaptchaResolverInterface
    {
        $resolver = strval(getenv('CAPTCHA_RESOLVER'));

        if ('console' === $resolver) {
            return new ConsoleCaptchaResolver();
        }

        if ('local' === $resolver) {
            return new CaptchaLocalResolver(
                new CaptchaLocalResolverClient(
                    strval(getenv('CAPTCHA_LOCAL_HOST')),
                    intval(getenv('CAPTCHA_LOCAL_PORT')),
                    intval(getenv('CAPTCHA_LOCAL_TIMEOUT')),
                    new Client()
                )
            );
        }

        if ('decaptcher' === $resolver) {
            return new DeCaptcherCaptchaResolver(
                new Client(),
                strval(getenv('DECAPTCHER_USERNAME')),
                strval(getenv('DECAPTCHER_PASSWORD'))
            );
        }

        if ('anticaptcha' === $resolver) {
            return AntiCaptchaResolver::create(
                strval(getenv('ANTICAPTCHA_CLIENT_KEY')),
                new Client(),
                intval(getenv('ANTICAPTCHA_CLIENT_TIMEOUT'))
            );
        }

        throw new RuntimeException('Unable to create resolver');
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function createSatSessionData(): SatSessionData
    {
        $rfc = strval(getenv('SAT_AUTH_RFC'));
        if ('' === $rfc) {
            throw new RuntimeException('The is no environment variable SAT_AUTH_RFC');
        }

        $ciec = strval(getenv('SAT_AUTH_CIEC'));
        if ('' === $ciec) {
            throw new RuntimeException('The is no environment variable SAT_AUTH_CIEC');
        }

        $resolver = static::createCaptchaResolver();

        return new SatSessionData($rfc, $ciec, $resolver);
    }

    public function createSatScraper(): SatScraper
    {
        $sessionData = $this->createSatSessionData();
        $cookieFile = __DIR__ . '/../../build/cookie-' . strtolower($sessionData->getRfc()) . '.json';
        $cookieJar = new FileCookieJar($cookieFile, true);
        $satHttpGateway = new SatHttpGateway($this->createGuzzleClient(), $cookieJar);
        return new SatScraper($sessionData, $satHttpGateway);
    }

    public function createGuzzleClient(): Client
    {
        $container = new HttpLogger(strval(getenv('SAT_HTTPDUMP_FOLDER')));
        $stack = HandlerStack::create();
        $stack->push(Middleware::history($container));
        return new Client(['handler' => $stack]);
    }

    public function createRepository(string $filename): Repository
    {
        if (! file_exists($filename)) {
            throw new RuntimeException(sprintf('The repository file %s was not found', $filename));
        }
        return Repository::fromFile($filename);
    }

    public function getSatScraper(): SatScraper
    {
        if (null === $this->scraper) {
            $this->scraper = $this->createSatScraper();
        }
        return $this->scraper;
    }

    public function getRepository(): Repository
    {
        if (null === $this->repository) {
            $this->repository = $this->createRepository($this->repositoryPath);
        }
        return $this->repository;
    }
}
