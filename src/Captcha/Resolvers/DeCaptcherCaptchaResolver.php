<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Captcha\Resolvers;

use GuzzleHttp\Client;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;

class DeCaptcherCaptchaResolver implements CaptchaResolverInterface
{
    public const URL_SERVICE = 'http://poster.de-captcher.com';

    public const METHOD_SERVICE_PICTURE = 'picture2';

    public const METHOD_SERVICE_BALANCE = 'balance';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var
     */
    protected $user;

    /**
     * @var
     */
    protected $password;

    /**
     * @var
     */
    protected $image;

    /**
     * CaptchaResolver constructor.
     *
     * @param Client $client
     * @param string $user
     * @param string $password
     */
    public function __construct(Client $client, string $user, string $password)
    {
        $this->client = $client;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @param string $imageBase64
     *
     * @return CaptchaResolverInterface
     */
    public function setImage(string $imageBase64): CaptchaResolverInterface
    {
        if (empty($imageBase64)) {
            throw new \InvalidArgumentException('The parameter imageBase64 is required');
        }

        $this->image = $imageBase64;

        return $this;
    }

    /**
     * @return string|null
     */
    public function decode(): ?string
    {
        $response = $this->client->post(
            self::URL_SERVICE,
            [
                'multipart' => [
                    [
                        'name' => 'pict',
                        'contents' => fopen("data://text/plain;base64,{$this->image}", 'r'),
                    ],
                    [
                        'name' => 'function',
                        'contents' => self::METHOD_SERVICE_PICTURE,
                    ],
                    [
                        'name' => 'username',
                        'contents' => $this->user,
                    ],
                    [
                        'name' => 'password',
                        'contents' => $this->password,
                    ],
                    [
                        'name' => 'pic_type',
                        'contents' => 0,
                    ],
                    [
                        'name' => 'text1',
                        'contents' => null,
                    ],
                ],
            ]
        )->getBody()
            ->getContents();

        $parts = explode('|', $response);
        if (0 !== (int)$parts[0]) {
            return null;
        }

        return (string)trim(end($parts));
    }
}
