<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Captcha\Resolvers;

use GuzzleHttp\Client;
use PhpCfdi\CfdiSatScraper\Contracts\CaptchaResolverInterface;

class DeCaptcherCaptchaResolver implements CaptchaResolverInterface
{
    public const URL_SERVICE = 'http://poster.de-captcher.com';

    public const METHOD_SERVICE_PICTURE = 'picture2';

    /**
     * @var Client
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

    public function decode(string $image): string
    {
        $response = $this->client->post(
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
        )->getBody()->getContents();

        $parts = explode('|', $response);
        if (0 !== (int) $parts[0]) { // bad answer
            return '';
        }

        return trim((string) end($parts));
    }
}
