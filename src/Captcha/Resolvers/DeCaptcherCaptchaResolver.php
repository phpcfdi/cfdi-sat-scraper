<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Captcha\Resolvers;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;

class DeCaptcherCaptchaResolver implements CaptchaResolverInterface
{
    public const URL_SERVICE = 'http://poster.de-captcher.com';

    public const METHOD_SERVICE_PICTURE = 'picture2';

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $user;

    /**
     * @var string
     */
    protected $password;

    /**
     * CaptchaResolver constructor.
     *
     * @param ClientInterface $client
     * @param string $user
     * @param string $password
     */
    public function __construct(ClientInterface $client, string $user, string $password)
    {
        $this->client = $client;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * @param string $image
     * @return string
     * @throws GuzzleException if an error on http transaction
     */
    public function decode(string $image): string
    {
        $response = $this->client->request(
            'POST',
            self::URL_SERVICE,
            [
                'multipart' => [
                    [
                        'name' => 'pict',
                        'contents' => fopen("data://text/plain;base64,{$image}", 'r'),
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
        );
        $parts = explode('|', strval($response->getBody()));
        if (0 !== (int) $parts[0]) { // bad answer
            return '';
        }

        return trim(end($parts) ?: '');
    }
}
