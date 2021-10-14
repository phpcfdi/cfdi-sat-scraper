<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions\Fiel;

use PhpCfdi\Credentials\Credential;

class FielSessionData
{
    /** @var Credential */
    private $fiel;

    public function __construct(Credential $fiel)
    {
        $this->fiel = $fiel;
    }

    public function getFiel(): Credential
    {
        return $this->fiel;
    }

    public function getRfc(): string
    {
        return $this->fiel->certificate()->rfc();
    }

    public function getValidTo(): string
    {
        return $this->fiel->certificate()->validTo();
    }

    public function getSerialNumber(): string
    {
        return $this->fiel->certificate()->serialNumber()->bytes();
    }

    public function sign(string $data, int $algorithm): string
    {
        return $this->fiel->privateKey()->sign($data, $algorithm);
    }
}
