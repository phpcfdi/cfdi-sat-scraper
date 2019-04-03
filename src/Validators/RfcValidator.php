<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Validators;

use PhpCfdi\CfdiSatScraper\Validators\Contracts\ValidatorInterface;

class RfcValidator implements ValidatorInterface
{
    /**
     * @var string
     */
    protected static $regex = '/^'
        . '[A-Za-z&ñÑ]{3,4}'
        . '(\d{2}((0[1-9]|1[012])(0[1-9]|1\d|2[0-8])|(0[13456789]|1[012])(29|30)|(0[13578]|1[02])31)|([02468][048]|[13579][26])0229)'
        . '([a-zA-Z0-9]{2}[0-9aA])'
        . '$/u';

    /**
     * @param string $key
     *
     * @return bool
     */
    public function can(string $key): bool
    {
        return 'rfc' === $key;
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function isValid($value): bool
    {
        return false !== preg_match(static::$regex, $value);
    }
}
