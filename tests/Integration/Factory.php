<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use LogicException;
use PhpCfdi\CfdiSatScraper\SatHttpGateway;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionData;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielSessionData;
use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielSessionManager;
use PhpCfdi\CfdiSatScraper\Sessions\SessionManager;
use PhpCfdi\Credentials\Credential;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use PhpCfdi\ImageCaptchaResolver\Resolvers\AntiCaptchaResolver;
use PhpCfdi\ImageCaptchaResolver\Resolvers\CaptchaLocalResolver;
use PhpCfdi\ImageCaptchaResolver\Resolvers\ConsoleResolver;
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
            return new ConsoleResolver();
        }

        if ('local' === $resolver) {
            return CaptchaLocalResolver::create(
                $this->env('CAPTCHA_LOCAL_URL'),
                intval($this->env('CAPTCHA_LOCAL_INITIAL_WAIT')) ?: CaptchaLocalResolver::DEFAULT_INITIAL_WAIT,
                intval($this->env('CAPTCHA_LOCAL_TIMEOUT')) ?: CaptchaLocalResolver::DEFAULT_TIMEOUT,
                intval($this->env('CAPTCHA_LOCAL_WAIT')) ?: CaptchaLocalResolver::DEFAULT_WAIT,
            );
        }

        if ('anticaptcha' === $resolver) {
            return AntiCaptchaResolver::create(
                $this->env('ANTICAPTCHA_CLIENT_KEY'),
                intval($this->env('ANTICAPTCHA_INITIAL_WAIT')) ?: AntiCaptchaResolver::DEFAULT_INITIAL_WAIT,
                intval($this->env('ANTICAPTCHA_TIMEOUT')) ?: AntiCaptchaResolver::DEFAULT_TIMEOUT,
                intval($this->env('ANTICAPTCHA_WAIT')) ?: AntiCaptchaResolver::DEFAULT_WAIT,
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
        $suffix = basename(str_replace(['\\', 'sessionmanager'], ['/', ''], strtolower(get_class($sessionManager))));
        $rfc = strtolower($sessionManager->getRfc());
        $cookieFile = sprintf('%s/%s/cookie-%s-%s.json', __DIR__, '../../build', $rfc, $suffix);
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
