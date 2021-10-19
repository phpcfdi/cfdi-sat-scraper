<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests;

use DateTimeImmutable;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Metadata;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\Tests\Integration\Factory;
use Throwable;

require __DIR__ . '/bootstrap.php';

exit(call_user_func(new class () {
    /** @var string */
    private $rfc;

    /** @var string */
    private $command;

    public function __invoke(string ...$arguments): int
    {
        try {
            $this->command = array_shift($arguments) ?: basename(__FILE__);
            if (count($arguments) < 2) {
                $this->printHelp();
                return 1;
            }
            if (in_array('-h', $arguments, true) || in_array('--help', $arguments, true)) {
                $this->printHelp();
                return 0;
            }
            $since = new DateTimeImmutable($arguments[0] ?? '');
            $until = new DateTimeImmutable($arguments[1] ?? '');

            $scraper = (new Factory('no-repository-file'))->createSatScraper();
            $this->rfc = $scraper->getSessionManager()->getRfc();

            $list = new MetadataList([]);

            $list = $list->merge(
                $scraper->listByPeriod(
                    (new QueryByFilters($since, $until))->setDownloadType(DownloadType::recibidos()),
                ),
            );
            $list = $list->merge(
                $scraper->listByPeriod(
                    (new QueryByFilters($since, $until))->setDownloadType(DownloadType::emitidos()),
                ),
            );
            $this->printList($list);
            return 0;
        } catch (Throwable $exception) {
            file_put_contents('php://stderr', $exception->getMessage() . PHP_EOL, FILE_APPEND);
            return 1;
        }
    }

    public function printList(MetadataList $list): void
    {
        $flagFirst = true;
        echo '['; // start of array
        foreach ($list as $metadata) {
            if ($flagFirst) {
                $flagFirst = false;
            } else {
                echo ',';
            }
            echo PHP_EOL, "\t", $this->metadataToJson($metadata);
        }
        echo PHP_EOL, ']', PHP_EOL; // end of array
    }

    public function metadataToJson(Metadata $metadata): string
    {
        return json_encode([
            'uuid' => $metadata->uuid(),
            'issuer' => $metadata->get('rfcEmisor'),
            'receiver' => $metadata->get('rfcReceptor'),
            'date' => $metadata->get('fechaEmision'),
            'type' => $metadata->get('rfcEmisor') === $this->rfc ? 'E' : 'R',
            'state' => $metadata->get('estadoComprobante'),
        ], JSON_THROW_ON_ERROR);
    }

    public function printHelp(): void
    {
        echo "$this->command start-date end-date", PHP_EOL,
            'start-date end-date are dates, time is ignored', PHP_EOL,
            'This script is a helper to retrieve issued and received cfdi on specific dates,', PHP_EOL,
            'the resulting json can be stored as a source of true to perform the integration tests.', PHP_EOL,
            'The configuration options are the same as used on integration tests', PHP_EOL;
    }
}, ...$argv));
