<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\CaptchaLocalResolver;

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;

class CaptchaLocalResolver implements CaptchaResolverInterface
{
    /** @var CaptchaLocalResolverClient */
    private $client;

    public function __construct(CaptchaLocalResolverClient $client)
    {
        $this->client = $client;
    }

    public function decode(string $image): string
    {
        return $this->client->resolveImage($image);
    }
}
