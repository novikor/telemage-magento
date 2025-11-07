<?php
declare(strict_types=1);

namespace Novikor\Telemage\Service\TelemageAPI\SDK;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class Client
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $integration
    ) {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function storeReferral(string $referralId, string $jwe): void
    {
        $url = sprintf(
            '/api/magento/login/referral/%s',
            rawurlencode($this->integration)
        );
        $response = $this->httpClient->request('POST', $url, [
            'json' => [
                'referralId' => $referralId,
                'jwe' => $jwe,
            ],
        ]);
        $response->getContent(throw: true);
    }
}
