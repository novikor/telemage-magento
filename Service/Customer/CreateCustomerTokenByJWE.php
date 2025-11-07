<?php
declare(strict_types=1);

namespace Novikor\Telemage\Service\Customer;

use Exception;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Integration\Api\TokenManager;
use Magento\Integration\Model\CustomUserContext;
use Novikor\Telemage\Api\Customer\CreateCustomerTokenByJWEInterface;
use Novikor\Telemage\Model\ConfigInterface;
use Override;

readonly class CreateCustomerTokenByJWE implements CreateCustomerTokenByJWEInterface
{
    public function __construct(
        private TokenManager $tokenManager,
        private JweToken $jweService,
        private AuthoriseCustomerById $authoriseCustomerById,
        private ManagerInterface $eventManager,
        private ConfigInterface $config
    ) {
    }

    /**
     * @throws AuthenticationException
     * @throws EmailNotConfirmedException
     * @throws \Magento\Framework\Webapi\Exception
     */
    #[Override]
    public function execute(string $jwe): string
    {
        if (!$this->config->isEnabled()) {
            throw new \Magento\Framework\Webapi\Exception(
                phrase: __('Telemage Integration is disabled.'),
                code: 0,
                httpCode: 403,
                name: 'integration_not_available'
            );
        }
        try {
            $id = $this->jweService->validateAndGetCustomerId($jwe);
            $customer = $this->authoriseCustomerById->execute($id);
        } catch (EmailNotConfirmedException $exception) {
            throw $exception;
        } catch (Exception $e) {
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                ),
                $e
            );
        }
        $this->eventManager->dispatch('customer_login', ['customer' => $customer]);

        return $this->generateCustomerAccessToken($id);
    }

    private function generateCustomerAccessToken(int $id): string
    {
        return $this->tokenManager->create(
            userContext: new CustomUserContext(
                userId: $id,
                userType: UserContextInterface::USER_TYPE_CUSTOMER
            ),
            params: $this->tokenManager->createUserTokenParameters()
        );
    }
}
