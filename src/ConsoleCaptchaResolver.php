<?php

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;

class ConsoleCaptchaResolver implements CaptchaResolverInterface
{
    /** @var string */
    private $image;

    public function __construct(string $image)
    {
        $this->setImage($image);
    }

    public function setImage(string $image): CaptchaResolverInterface
    {
        return $this;
    }

    public function decode(): ?string
    {
        $temporaryFile = $this->storeImage();
        $this->writeLine("\nResolve the captcha stored on file $temporaryFile: ");
        $decoded = $this->readLine();
        if (file_exists($temporaryFile)) {
            unlink($temporaryFile);
        }
        return $decoded;
    }

    public function storeImage(): string
    {
        if ('' === $this->image) {
            throw new \RuntimeException('Current image is empty');
        }
        $filename = tempnam('', 'captcha-');
        file_put_contents($filename, $this->image);
        return $filename;
    }

    protected function writeLine(string $message): void
    {
        echo $message;
    }

    protected function readLine(): string
    {
        return trim(fgets(STDIN) ?: '');
    }
}
