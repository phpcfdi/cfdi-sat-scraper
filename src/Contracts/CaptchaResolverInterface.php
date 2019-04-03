<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

interface CaptchaResolverInterface
{
    /**
     * @param string $image
     *
     * @return CaptchaResolverInterface
     */
    public function setImage(string $image): self;

    /**
     * @return string|null
     */
    public function decode(): ?string;
}
