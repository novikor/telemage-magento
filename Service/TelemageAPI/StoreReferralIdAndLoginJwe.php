<?php
declare(strict_types=1);

namespace Novikor\Telemage\Service\TelemageAPI;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class StoreReferralIdAndLoginJwe
{
    public function __construct(
        private ApiClientFlyweightFactoryInterface $apiClientFlyweightFactory,
        private StoreManagerInterface $storeManager,
    ) {
    }

    /**
     * @throws ConfigurationMismatchException
     * @throws LocalizedException
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function execute(string $jwe): string
    {
        $referralId = uniqid();
        $this->apiClientFlyweightFactory
            ->get($this->storeManager->getWebsite()->getId())
            ->storeReferral($referralId, $jwe);

        return $referralId;
    }
}
