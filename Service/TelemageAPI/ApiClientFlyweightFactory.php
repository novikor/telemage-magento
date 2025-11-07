<?php
declare(strict_types=1);

namespace Novikor\Telemage\Service\TelemageAPI;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Novikor\Telemage\Model\ConfigInterface;
use Novikor\Telemage\Service\TelemageAPI\SDK\Client;
use Symfony\Component\HttpClient\HttpClient;

class ApiClientFlyweightFactory implements ApiClientFlyweightFactoryInterface
{
    /** @var array<int|string, SDK\Client> */
    private array $clientsPool = [];

    public function __construct(
        private readonly ConfigInterface $config
    ) {
    }

    /**
     * @throws ConfigurationMismatchException
     */
    #[\Override]
    public function get(int|string $websiteId): Client
    {
        if (!$this->config->isConfigured($websiteId)) {
            throw new ConfigurationMismatchException(__('Telemage is not configured'));
        }
        return $this->clientsPool[$websiteId] ??= $this->createClient($websiteId);
    }

    private function createClient(int|string $websiteId): Client
    {
        return new Client(
            httpClient: HttpClient::createForBaseUri($this->config->getApiBaseUrl($websiteId), [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ]
            ]),
            integration: $this->config->getIntegrationUrlToken($websiteId),
        );
    }
}
