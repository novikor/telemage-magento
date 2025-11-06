<?php

declare(strict_types=1);

namespace Novikor\Telemage\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config implements ConfigInterface
{
    private const string XML_PATH_TELEMAGE_GENERAL_ENABLED = 'telemage/general/enabled';
    private const string XML_PATH_TELEMAGE_GENERAL_BOT_TOKEN = 'telemage/general/bot_token';
    private const string XML_PATH_TELEMAGE_GENERAL_JWE_SECRET = 'telemage/general/jwe_secret';

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    #[\Override]
    public function isEnabled(int|string|null $websiteId = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_TELEMAGE_GENERAL_ENABLED,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    #[\Override]
    public function getBotToken(int|string|null $websiteId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TELEMAGE_GENERAL_BOT_TOKEN,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    #[\Override]
    public function getJweSecret(int|string|null $websiteId = null): ?string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TELEMAGE_GENERAL_JWE_SECRET,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}
