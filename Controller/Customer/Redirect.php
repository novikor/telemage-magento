<?php

declare(strict_types=1);

namespace Novikor\Telemage\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Novikor\Telemage\Model\ConfigInterface;
use Novikor\Telemage\Service\Customer\JweToken;
use Novikor\Telemage\Service\TelemageAPI\StoreReferralIdAndLoginJwe;
use Psr\Log\LoggerInterface;

class Redirect implements HttpGetActionInterface
{
    public function __construct(
        private readonly Context $context,
        private readonly ConfigInterface $config,
        private readonly JweToken $jweToken,
        private readonly Session $customerSession,
        private readonly StoreReferralIdAndLoginJwe $storeReferralIdAndLoginJwe,
        private readonly LoggerInterface $logger
    ) {
    }

    #[\Override]
    public function execute(): \Magento\Framework\Controller\Result\Redirect
    {
        $redirect = $this->context->getResultRedirectFactory()->create();
        if (!$this->config->isConfigured()) {
            return $redirect->setPath('customer/account');
        }

        try {
            $botName = $this->config->getBotToken();
            $jwe = $this->jweToken->generateForCustomer((int)$this->customerSession->getCustomerId());
            $referralId = $this->storeReferralIdAndLoginJwe->execute($jwe);
            $redirect->setUrl(sprintf('https://t.me/%s?start=%s', $botName, $referralId));
        } catch (\Throwable $e) {
            $redirect->setPath('telemage/customer');
            $this->context->getMessageManager()->addErrorMessage(
                __('Something went wrong. Please try again later.')->render()
            );
            $this->logger->critical($e->__toString());
        }

        return $redirect;
    }
}
