<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Validators;

use PhpCfdi\CfdiSatScraper\Validators\Contracts\ValidatorInterface;

class CiecValidator implements ValidatorInterface
{
    /**
     * @param string $key
     *
     * @return bool
     */
    public function can(string $key): bool
    {
        return 'ciec' === $key;
    }

    /**
     * @param  $value
     * @return bool
     */
    public function isValid($value): bool
    {
        return ! empty($value);
    }
}
