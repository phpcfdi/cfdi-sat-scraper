<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Contracts;

/**
 * This kind of objects are created to connect to the captcha resolver solution.
 */
interface CaptchaResolverInterface
{
    /**
     * Resolve the image (base64 encoded) and return the answer as string.
     * If the string is empty means that the service was unable to find a solution.
     *
     * @param string $base64Image
     * @return string
     */
    public function decode(string $base64Image): string;
}
