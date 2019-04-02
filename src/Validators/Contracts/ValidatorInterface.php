<?php
declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Validators\Contracts;

interface ValidatorInterface
{
    /**
     * @param string $key
     * @return bool
     */
    public function can(string $key): bool;

    /**
     * @param $value
     * @return bool
     */
    public function isValid($value): bool;
}
