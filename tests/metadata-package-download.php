<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests;

use PhpCfdi\CfdiSatScraper\MetadataPackage\AvailablePackageList;
use PhpCfdi\CfdiSatScraper\MetadataPackageScraper;
use PhpCfdi\CfdiSatScraper\Tests\Integration\Factory;
use RuntimeException;
use Throwable;

require __DIR__ . '/bootstrap.php';

exit(call_user_func(new class () {
    private string $command = '';

    private MetadataPackageScraper|null $scraper = null;

    private AvailablePackageList|null $available = null;

    public function printHelp(): void
    {
        echo "$this->command Download one package or all available packages", PHP_EOL,
        '  Usage:', PHP_EOL,
        "    $this->command package-uuid destination-file", PHP_EOL,
        "    $this->command all destination-folder", PHP_EOL,
        '  This script is a helper to download a metadata package or all packages.', PHP_EOL,
        '  The configuration options are the same as used on integration tests', PHP_EOL;
    }

    public function __invoke(string ...$arguments): int
    {
        $this->command = array_shift($arguments) ?: basename(__FILE__);
        if (count($arguments) < 2) {
            $this->printHelp();
            return 1;
        }
        if (in_array('-h', $arguments, true) || in_array('--help', $arguments, true)) {
            $this->printHelp();
            return 0;
        }
        try {
            $uuid = $arguments[0] ?? '';
            if ('' === $uuid) {
                throw new RuntimeException('Argument UUID is missing');
            }

            $destination = $arguments[1] ?? '';
            if ('' === $destination) {
                throw new RuntimeException('Argument destination file is missing');
            }

            if (0 === strcmp('all', $uuid)) {
                $this->checkDestinationDirectory($destination);
                $this->downloadAll($destination);
            } else {
                $this->printAvailablePackages();
                $this->downloadPackage($uuid, $destination);
            }

            return 0;
        } catch (Throwable $exception) {
            file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL, FILE_APPEND);
            return 1;
        }
    }

    public function obtainScraper(): MetadataPackageScraper
    {
        if (null === $this->scraper) {
            $basescraper = (new Factory('no-repository-file'))->createSatScraper();
            $this->scraper = new MetadataPackageScraper($basescraper->getSessionManager(), $basescraper->getSatHttpGateway());
        }
        return $this->scraper;
    }

    public function obtainAvailablePackages(): AvailablePackageList
    {
        if (null === $this->available) {
            $scraper = $this->obtainScraper();
            $this->available = $scraper->listAvailablePackages();
        }
        return $this->available;
    }

    public function checkDestinationDirectory(string $destinationPath): void
    {
        if (! is_dir($destinationPath)) {
            throw new RuntimeException('Destination directory does not exist');
        } elseif (! is_writable($destinationPath)) {
            throw new RuntimeException('Destination directory is not writable');
        }
    }

    public function printAvailablePackages(): void
    {
        $available = $this->obtainAvailablePackages();
        echo 'Paquetes disponibles:', PHP_EOL;
        foreach ($available as $package) {
            echo '  ', $package->getUuid(), PHP_EOL;
        }
    }

    public function downloadAll(string $destinationPath): void
    {
        $this->printAvailablePackages();
        $available = $this->obtainAvailablePackages();
        foreach ($available as $package) {
            $this->downloadPackage($package->getUuid(), sprintf('%s/%s.zip', $destinationPath, strtolower($package->getUuid())));
        }
    }

    public function downloadPackage(string $uuid, string $destination): void
    {
        $this->checkDestinationDirectory(dirname($destination));
        $scraper = $this->obtainScraper();
        $available = $this->obtainAvailablePackages();
        echo 'Paquete ', $uuid;
        if ($available->has($uuid)) {
            $scraper->downloadPackageFromAvailable($available, $uuid, $destination);
            echo ' no existe en los paquetes disponibles', PHP_EOL;
        } else {
            echo ' descargado en ', $destination, PHP_EOL;
        }
    }
}, ...($argv ?? [])));
