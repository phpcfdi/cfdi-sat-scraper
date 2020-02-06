<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\CaptchaLocalResolver;

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;

class CaptchaLocalResolver implements CaptchaResolverInterface
{
    /** @var CaptchaLocalResolverClient */
    private $client;

    /** @var string */
    private $image;

    public function __construct(CaptchaLocalResolverClient $client)
    {
        $this->client = $client;
    }

    public function setImage(string $image): CaptchaResolverInterface
    {
        $this->image = $image;
        return $this;
    }

    public function decode(): ?string
    {
        return $this->client->resolveImage($this->image);
    }
}
