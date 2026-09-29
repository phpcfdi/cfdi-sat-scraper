<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\MetadataPackageScraper;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\Tests\Integration\Factory;
use RuntimeException;
use Throwable;

require __DIR__ . '/bootstrap.php';

exit(call_user_func(new class () {
    private string $command = '';

    public function printHelp(): void
    {
        echo "$this->command received|issued start-date end-date", PHP_EOL,
        '  received|issued defines if query will be received or issued', PHP_EOL,
        '  start-date end-date are dates, time is ignored, query is for full months', PHP_EOL,
        '  This script is a helper to create a metadata package for issued or received cfdi on specific dates.', PHP_EOL,
        '  The configuration options are the same as used on integration tests', PHP_EOL;
    }

    public function __invoke(string ...$arguments): int
    {
        $this->command = basename(array_shift($arguments) ?: __FILE__);
        if (count($arguments) < 3) {
            $this->printHelp();
            return 1;
        }
        if (in_array('-h', $arguments, true) || in_array('--help', $arguments, true)) {
            $this->printHelp();
            return 0;
        }
        try {
            $downloadType = $this->inputCreateDownloadType($arguments[0] ?? '');
            $since = new DateTimeImmutable($arguments[1] ?? '');
            $until = new DateTimeImmutable($arguments[2] ?? '');

            $basescraper = (new Factory('no-repository-file'))->createSatScraper();
            $scraper = new MetadataPackageScraper($basescraper->getSessionManager(), $basescraper->getSatHttpGateway());
            $query = new QueryByFilters($since, $until, $downloadType);

            echo 'Consulta:', PHP_EOL,
            '  Desde: ', $query->getStartDate()->format('Y-m-d'), PHP_EOL,
            '  Hasta: ', $query->getEndDate()->format('Y-m-d'), PHP_EOL,
            '  Tipo: ', $query->getDownloadType()->isEmitidos() ? 'Emitidos' : 'Recibidos', PHP_EOL;

            $results = $scraper->requestByMonths($query);
            echo 'Paquetes generados:', PHP_EOL;
            foreach ($results as $result) {
                echo '  ', $result->getStartDate()->format('Y-m-d H:i:s'),
                ' ', $result->getEndDate()->format('Y-m-d H:i:s'),
                ' ', $result->hasUuid() ? $result->getUuid() : 'Sin resultados',
                PHP_EOL;
            }

            return 0;
        } catch (Throwable $exception) {
            file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL, FILE_APPEND);
            return 1;
        }
    }

    public function inputCreateDownloadType(string $input): DownloadType
    {
        return match (substr($input, 0, 1)) {
            'i' => DownloadType::emitidos(),
            'r' => DownloadType::recibidos(),
            default => throw new RuntimeException('Invalid download type'),
        };
    }
}, ...($argv ?? [])));
