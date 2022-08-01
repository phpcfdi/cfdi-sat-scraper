<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use LogicException;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Internal\MetadataExtractor;
use Traversable;

/**
 * The Metadata class is a storage of values retrieved from the list of documents obtained from
 * a search on the SAT CFDI Portal.
 *
 * It always has a UUID, all other properties are optional.
 *
 * @property-read string $uuid Folio Fiscal
 * @property-read string $rfcEmisor RFC Emisor
 * @property-read string $nombreEmisor Nombre o Razón Social del Emisor
 * @property-read string $rfcReceptor RFC Receptor
 * @property-read string $nombreReceptor Nombre o Razón Social del Receptor
 * @property-read string $fechaEmision Fecha de Emisión
 * @property-read string $fechaCertificacion Fecha de Certificación
 * @property-read string $pacCertifico PAC que Certificó
 * @property-read string $total Total
 * @property-read string $efectoComprobante Efecto del Comprobante
 * @property-read string $estatusCancelacion Estatus de cancelación
 * @property-read string $estadoComprobante Estado del Comprobante
 * @property-read string $estatusProcesoCancelacion Estatus de Proceso de Cancelación
 * @property-read string $fechaProcesoCancelacion Fecha de Proceso de Cancelación
 * @property-read string $rfcACuentaTerceros RFC a cuenta de terceros
 *
 * @see MetadataExtractor::defaultFieldsCaptions()
 * @implements IteratorAggregate<string, string>
 */
class Metadata implements JsonSerializable, IteratorAggregate
{
    /** @var array<string, string> */
    private $data;

    /**
     * Metadata constructor.
     * $uuid will be converted to lower case.
     * If $data contains a key with 'uuid' will be ignored.
     *
     * @param string $uuid
     * @param array<string, string> $data
     * @throws InvalidArgumentException when UUID is empty
     */
    public function __construct(string $uuid, array $data = [])
    {
        if ('' === $uuid) {
            throw InvalidArgumentException::emptyInput('UUID');
        }
        $this->data = ['uuid' => strtolower($uuid)] + $data;
    }

    public function __get(string $name): string
    {
        return $this->get($name);
    }

    public function __isset(string $name): bool
    {
        return $this->has($name);
    }

    /** @param mixed $value */
    public function __set(string $name, $value): void
    {
        throw new LogicException(sprintf('The %s class is immutable', self::class));
    }

    public function __unset(string $name): void
    {
        throw new LogicException(sprintf('The %s class is immutable', self::class));
    }

    public function uuid(): string
    {
        return $this->data['uuid'];
    }

    public function get(string $key): string
    {
        return strval($this->data[$key] ?? '');
    }

    /** @return array<string, string> */
    public function getData(): array
    {
        return $this->data;
    }

    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    public function getResource(ResourceType $resourceType): string
    {
        return $this->get($resourceType->value());
    }

    public function hasResource(ResourceType $resourceType): bool
    {
        return '' !== $this->get($resourceType->value());
    }

    /** @return Traversable<string, string> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /** @return array<string, string> */
    public function jsonSerialize(): array
    {
        return $this->data;
    }
}
