<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper;

use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;
use PhpCfdi\CfdiSatScraper\Exceptions\InvalidArgumentException;
use PhpCfdi\CfdiSatScraper\Exceptions\LoginException;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\Internal\MetadataDownloader;
use PhpCfdi\CfdiSatScraper\Internal\QueryResolver;
use PhpCfdi\CfdiSatScraper\Internal\SatSessionManager;

class SatScraper
{
    /** @var SatSessionManager */
    private $satSession;

    /** @var callable|null */
    protected $onFiveHundred;

    /** @var SatHttpGateway */
    private $satHttpGateway;

    /**
     * SatScraper constructor.
     *
     * @param string $rfc
     * @param string $ciec
     * @param CaptchaResolverInterface $captchaResolver
     * @param SatHttpGateway|null $satHttpGateway
     *
     * @throws InvalidArgumentException when RFC is an empty string
     * @throws InvalidArgumentException when CIEC is an empty string
     * @throws InvalidArgumentException when Login URL is not a valid url
     */
    public function __construct(
        string $rfc,
        string $ciec,
        CaptchaResolverInterface $captchaResolver,
        ?SatHttpGateway $satHttpGateway = null
    ) {
        $this->satHttpGateway = $satHttpGateway ?? new SatHttpGateway();
        $this->satSession = $this->createSessionManager($rfc, $ciec, $captchaResolver, $satHttpGateway);
    }

    protected function createSessionManager(
        string $rfc,
        string $ciec,
        CaptchaResolverInterface $captchaResolver,
        SatHttpGateway $satHttpGateway
    ): SatSessionManager
    {
        return new SatSessionManager($rfc, $ciec, URLS::SAT_URL_LOGIN, $satHttpGateway, $captchaResolver, 3, 3);
    }

    public function getRfc(): string
    {
        return $this->satSession->getRfc();
    }

    public function getLoginUrl(): string
    {
        return $this->satSession->getLoginUrl();
    }

    /**
     * Change the current Login URL to a new destination
     *
     * @param string $loginUrl
     * @return $this
     * @throws InvalidArgumentException when Login URL is not a valid url
     */
    public function setLoginUrl(string $loginUrl): self
    {
        $this->satSession->setLoginUrl($loginUrl);
        return $this;
    }

    public function getCaptchaResolver(): CaptchaResolverInterface
    {
        return $this->satSession->getCaptchaResolver();
    }

    public function setCaptchaResolver(CaptchaResolverInterface $captchaResolver): self
    {
        $this->satSession->setCaptchaResolver($captchaResolver);

        return $this;
    }

    public function getMaxTriesCaptcha(): int
    {
        return $this->satSession->getMaxTriesCaptcha();
    }

    public function setMaxTriesCaptcha(int $maxTriesCaptcha): self
    {
        $this->satSession->setMaxTriesCaptcha($maxTriesCaptcha);

        return $this;
    }

    public function getMaxTriesLogin(): int
    {
        return $this->satSession->getMaxTriesLogin();
    }

    public function setMaxTriesLogin(int $maxTriesLogin): self
    {
        $this->satSession->setMaxTriesLogin($maxTriesLogin);

        return $this;
    }

    public function getSatHttpGateway(): SatHttpGateway
    {
        return $this->satHttpGateway;
    }

    public function setSatHttpGateway(SatHttpGateway $satHttpGateway): self
    {
        $this->satHttpGateway = $satHttpGateway;

        return $this;
    }

    public function getOnFiveHundred(): ?callable
    {
        return $this->onFiveHundred;
    }

    public function setOnFiveHundred(?callable $callback): self
    {
        $this->onFiveHundred = $callback;

        return $this;
    }

    /**
     * Initializates session on SAT
     *
     * @return SatScraper
     * @throws LoginException
     */
    public function confirmSessionIsAlive(): self
    {
        $this->satSession->initSession();

        return $this;
    }

    public function createMetadataDownloader(): MetadataDownloader
    {
        return new MetadataDownloader(
            new QueryResolver($this->satHttpGateway),
            $this->onFiveHundred
        );
    }

    /**
     * Retrieve the MetadataList using specific UUIDS to download
     *
     * @param array $uuids
     * @param DownloadTypesOption $downloadType
     * @return MetadataList
     * @throws LoginException
     */
    public function downloadListUUID(array $uuids, DownloadTypesOption $downloadType): MetadataList
    {
        $this->confirmSessionIsAlive();
        return $this->createMetadataDownloader()->downloadByUuids($uuids, $downloadType);
    }

    /**
     * Retrieve the MetadataList based on the query, but uses full days on dates (without time parts)
     *
     * @param Query $query
     * @return MetadataList
     * @throws LoginException
     */
    public function downloadPeriod(Query $query): MetadataList
    {
        $this->confirmSessionIsAlive();
        return $this->createMetadataDownloader()->downloadByDate($query);
    }

    /**
     * Retrieve the MetadataList based on the query, but uses the period considering dates and times
     *
     * @param Query $query
     * @return MetadataList
     * @throws LoginException
     */
    public function downloadByDateTime(Query $query): MetadataList
    {
        $this->confirmSessionIsAlive();
        return $this->createMetadataDownloader()->downloadByDateTime($query);
    }

    /**
     * Create a DownloadXml object with (optionally) a MetadataList.
     * The DownloadXml object can be used to retrieve the CFDI XML contents.
     *
     * @param MetadataList|null $metadataList
     * @return DownloadXml
     */
    public function downloader(?MetadataList $metadataList = null): DownloadXml
    {
        return new DownloadXml($this->satHttpGateway, $metadataList);
    }
}
