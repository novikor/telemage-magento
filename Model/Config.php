<?php

declare(strict_types=1);

namespace Novikor\Telemage\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{
    private const string XML_PATH_ENABLED = 'telemage/general/enabled';
    private const string XML_PATH_BOT_TOKEN = 'telemage/general/bot_id';
    private const string XML_PATH_JWE_SECRET = 'telemage/general/jwe_secret';
    private const string XML_PATH_API_INTEGRATION_URL_TOKEN = 'telemage/api/integration_url_token';
    private const string XML_PATH_API_BASE_URL = 'telemage/api/base_url';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    #[\Override]
    public function isEnabled(int|string|null $websiteId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    #[\Override]
    public function getBotToken(int|string|null $websiteId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_BOT_TOKEN,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    #[\Override]
    public function getIntegrationUrlToken(int|string|null $websiteId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_API_INTEGRATION_URL_TOKEN,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    #[\Override]
    public function getJweSecret(int|string|null $websiteId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_JWE_SECRET,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    #[\Override]
    public function getApiBaseUrl(int|string|null $websiteId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_API_BASE_URL,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    #[\Override]
    public function isConfigured(int|string|null $websiteId = null): bool
    {
        return $this->isEnabled($websiteId)
            && $this->getBotToken($websiteId)
            && $this->getJweSecret($websiteId)
            && $this->getIntegrationUrlToken($websiteId)
            && $this->getApiBaseUrl($websiteId);
    }
}
