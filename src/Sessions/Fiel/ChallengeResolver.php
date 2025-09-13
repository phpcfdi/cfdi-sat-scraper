<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Sessions\Fiel;

use PhpCfdi\CfdiSatScraper\Internal\HtmlForm;

/**
 * Method extraction to resolve challenge using FIEL
 *
 * @see FielSessionManager::resolveChallengeUsingFiel
 * @internal
 */
final readonly class ChallengeResolver
{
    /** @param array<string, string> $fields */
    private function __construct(private array $fields, private FielSessionData $sessionData)
    {
    }

    public static function createFromHtml(string $html, FielSessionData $sessionData): self
    {
        $inputs = (new HtmlForm($html, '#certform'))->getFormValues();
        if (isset($inputs[''])) {
            unset($inputs['']);
        }
        return new self($inputs, $sessionData);
    }

    /**
     * @return array<string, string>
     */
    public function obtainFormFields(): array
    {
        return array_merge($this->fields, [
            'token' => $this->obtainTokenFromTokenUuid(),
            'guid' => $this->getTokenUuid(),
            'fert' => $this->sessionData->getValidTo(),
        ]);
    }

    public function obtainTokenFromTokenUuid(): string
    {
        $fiel = $this->getSessionData();
        $rfc = $fiel->getRfc();
        $serial = $fiel->getSerialNumber();
        $sourceString = "{$this->getTokenUuid()}|$rfc|$serial";
        $signature = base64_encode(base64_encode($fiel->sign($sourceString, OPENSSL_ALGO_SHA1)));
        return base64_encode(base64_encode($sourceString) . '#' . $signature);
    }

    public function getTokenUuid(): string
    {
        return ($this->fields['guid'] ?? '') ?: '';
    }

    public function getSessionData(): FielSessionData
    {
        return $this->sessionData;
    }
}
