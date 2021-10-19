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
use LogicException;
use PhpCfdi\CfdiSatScraper\Captcha\Resolvers\AntiCaptchaResolver;
use PhpCfdi\CfdiSatScraper\Captcha\Resolvers\ConsoleCaptchaResolver;
use PhpCfdi\CfdiSatScraper\Captcha\Resolvers\DeCaptcherCaptchaResolver;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionData;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielSessionData;
use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielSessionManager;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;
use PhpCfdi\CfdiSatScraper\Tests\CaptchaLocalResolver\CaptchaLocalResolver;
use PhpCfdi\CfdiSatScraper\Tests\CaptchaLocalResolver\CaptchaLocalResolverClient;
use PhpCfdi\Credentials\Credential;
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

    public function createCaptchaResolver(): CaptchaResolverInterface
    {
        $resolver = $this->env('CAPTCHA_RESOLVER');

        if ('console' === $resolver) {
            return new ConsoleCaptchaResolver();
        }

        if ('local' === $resolver) {
            return new CaptchaLocalResolver(
                new CaptchaLocalResolverClient(
                    $this->env('CAPTCHA_LOCAL_HOST'),
                    intval($this->env('CAPTCHA_LOCAL_PORT')),
                    intval($this->env('CAPTCHA_LOCAL_TIMEOUT')),
                    new Client()
                )
            );
        }

        if ('decaptcher' === $resolver) {
            return new DeCaptcherCaptchaResolver(
                new Client(),
                $this->env('DECAPTCHER_USERNAME'),
                $this->env('DECAPTCHER_PASSWORD')
            );
        }

        if ('anticaptcha' === $resolver) {
            return AntiCaptchaResolver::create(
                $this->env('ANTICAPTCHA_CLIENT_KEY'),
                new Client(),
                intval($this->env('ANTICAPTCHA_CLIENT_TIMEOUT'))
            );
        }

        throw new RuntimeException('Unable to create resolver');
    }

    public function createSessionManager(): SessionManager
    {
        $satAuthMode = $this->env('SAT_AUTH_MODE');
        if ('FIEL' === $satAuthMode) {
            return $this->createFielSessionManager();
        }
        if ('CIEC' === $satAuthMode) {
            return $this->createCiecSessionManager();
        }
        throw new LogicException("Unable to create a session manager using SAT_AUTHMODE='$satAuthMode'");
    }

    public function createFielSessionManager(): FielSessionManager
    {
        return new FielSessionManager($this->createFielSessionData());
    }

    public function createFielSessionData(): FielSessionData
    {
        $fiel = Credential::openFiles(
            $this->path($this->env('SAT_FIEL_CER')),
            $this->path($this->env('SAT_FIEL_KEY')),
            file_get_contents($this->path($this->env('SAT_FIEL_PWD'))) ?: '',
        );
        if (! $fiel->isFiel()) {
            throw new LogicException('The CERTIFICATE is not a FIEL');
        }
        if (! $fiel->certificate()->validOn()) {
            throw new LogicException('The CERTIFICATE is not valid');
        }

        return new FielSessionData($fiel);
    }

    public function createCiecSessionManager(): CiecSessionManager
    {
        return new CiecSessionManager($this->createCiecSessionData());
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function createCiecSessionData(): CiecSessionData
    {
        $rfc = $this->env('SAT_AUTH_RFC');
        if ('' === $rfc) {
            throw new RuntimeException('The is no environment variable SAT_AUTH_RFC');
        }

        $ciec = $this->env('SAT_AUTH_CIEC');
        if ('' === $ciec) {
            throw new RuntimeException('The is no environment variable SAT_AUTH_CIEC');
        }

        $resolver = $this->createCaptchaResolver();

        return new CiecSessionData($rfc, $ciec, $resolver);
    }

    public function createSatScraper(?SessionManager $sessionManager = null): SatScraper
    {
        $sessionManager = $sessionManager ?? $this->createSessionManager();
        $cookieFile = __DIR__ . '/../../build/cookie-' . strtolower($sessionManager->getRfc()) . '.json';
        $cookieJar = new FileCookieJar($cookieFile, true);
        $satHttpGateway = new SatHttpGateway($this->createGuzzleClient(), $cookieJar);
        return new SatScraper($sessionManager, $satHttpGateway);
    }

    public function createGuzzleClient(): Client
    {
        $container = new HttpLogger($this->path($this->env('SAT_HTTPDUMP_FOLDER')));
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

    public function env(string $variable): string
    {
        return strval($_SERVER[$variable] ?? '');
    }

    public function path(string $path): string
    {
        // if is not empty and is not an absolute path, prepend project dir
        if ('' !== $path && ! in_array(substr($path, 0, 1), ['/', '\\'], true)) {
            $path = dirname(__DIR__, 2) . '/' . $path;
        }
        return $path;
    }
}
