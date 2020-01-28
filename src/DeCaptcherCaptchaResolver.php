<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use GuzzleHttp\Client;

/**
 * @deprecated Use Captcha\Resolvers\DeCaptchaResolver, is the same but exists different namespace
 */
class DeCaptcherCaptchaResolver extends Captcha\Resolvers\DeCaptcherCaptchaResolver
{
    public function __construct(Client $client, string $user, string $password)
    {
        trigger_error(
            sprintf('%s is deprecated, use %s', get_class($this), Captcha\Resolvers\DeCaptcherCaptchaResolver::class),
            E_USER_DEPRECATED
        );

        parent::__construct($client, $user, $password);
    }
}
