<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Sessions\Fiel;

use PhpCfdi\Credentials\Credential;

trait CreateFakeFielTrait
{
    private function createFakeFiel(): Credential
    {
        return Credential::openFiles(
            $this->filePath('fake-fiel/EKU9003173C9.cer'),
            $this->filePath('fake-fiel/EKU9003173C9.key'),
            trim($this->fileContentPath('fake-fiel/EKU9003173C9.pwd')),
        );
    }
}
