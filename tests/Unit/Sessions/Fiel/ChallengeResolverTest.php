<?php

declare(strict_types=1);

namespace PhpCfdi\CfdiSatScraper\Tests\Unit\Sessions\Fiel;

use PhpCfdi\CfdiSatScraper\Sessions\Fiel\ChallengeResolver;
use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielSessionData;
use PhpCfdi\CfdiSatScraper\Tests\TestCase;

final class ChallengeResolverTest extends TestCase
{
    use CreateFakeFielTrait;

    public function testResolveChallenge(): void
    {
        $sessionData = new FielSessionData($this->createFakeFiel());
        $html = $this->fileContentPath('sample-response-fiel-login-form.html');

        $challengeResult = <<< EOF
            VFdwa2EwOUhWVFZhYWtWMFQxUm9hMWw1TURCUFJGVXlURmRGTTA1WFdYUk9iVmwzV1ZSSmVrNUhW
            VEpPYW14dGZFVkxWVGt3TURNeE56TkRPWHd6TURBd01UQXdNREF3TURRd01EQXdNalF4Tnc9PSNa
            VXhDYkZWR1NXNUxXSEIzT1VOWE1XYzRZa2xKVVhsWlVTOTVabVJEUmxRM2JqQjJhbk0yVFRSQ2RE
            VlllR1kwY21sV1VGSlVhV2xhVkU1dlRGQklTMW8xZVN0RlMyRXJjWEJGYXpkemQyOWFWek53Vlds
            blR6UnhMMHBxYWxGUmRtZzJOVVpMZWtnMlNuVmlTbTVxUmxGUlV6WlVTRlo1U0cxQk0yRm1URzkw
            ZFV4S2FISlRUazlQTjFaVldsSlNkVk5MUW5STVNHeG9aR1pFTWl0S1VsRkNhM2xaVVdkWlVEbHBk
            SFIxVUZCdWVXWk9hWEk0T0RReFpuRXZNRnBvUjBrelpsSXpZbXBuZUhOV2VrOXhhVVp5UWxFelVY
            VmlaSE5HYlhwbk1HWm9OME5PSzIwclQzQm1RWEp5WW5sM2F6azVSbmRRYXl0MVRGWlJSbloxTVd0
            VlFtOHhLMk0xVTNwUE4xUjRSV2RIWjB4UFJpdDFMMmt3TmtWMWJFMXhSazB3VjJnMllXaGFWMmRh
            UW5od01HOXFaRFpEWWxSRFdXOVFjVzVpWlVsYVZISkpieXR2WjIxS2NrY3JXaTh2WlZJMGNITjNQ
            VDA9
            EOF;
        $expectedFields = [
            'token' => str_replace("\n", '', $challengeResult),
            'credentialsRequired' => 'CERT',
            'guid' => $token = 'MjdkOGU5ZjEtOThkYy00ODU2LWE3NWYtNmYwYTIzNGU2Njlm',
            'ks' => 'null',
            'seeder' => '',
            'arc' => '',
            'tan' => '',
            'placer' => '',
            'secuence' => '',
            'urlApplet' => 'https://cfdiau.sat.gob.mx/nidp/app/login?id=SATx509Custom',
            'fert' => $sessionData->getValidTo(),
        ];

        $resolver = ChallengeResolver::createFromHtml($html, $sessionData);
        $this->assertSame($token, $resolver->getTokenUuid());
        $this->assertSame($sessionData, $resolver->getSessionData());
        $formFields = $resolver->obtainFormFields();
        $this->assertSame($expectedFields, $formFields);
    }
}
