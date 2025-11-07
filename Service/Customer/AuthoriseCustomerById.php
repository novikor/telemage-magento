<?php
declare(strict_types=1);

namespace Novikor\Telemage\Service\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\AuthenticationInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;

/**
 * @internal
 */
readonly class AuthoriseCustomerById
{
    public function __construct(
        private CustomerRepository $customerRepository,
        private AuthenticationInterface $authentication,
        private AccountConfirmation $accountConfirmation,
        private ManagerInterface $eventManager
    ) {
    }

    /**
     * Authenticate a customer by ID
     *
     * @throws LocalizedException
     */
    public function execute(int $id): CustomerInterface
    {
        $customer = $this->customerRepository->getById($id);

        if ($this->authentication->isLocked($id)) {
            throw new UserLockedException(__('The account is locked.'));
        }

        if ($this->isConfirmationRequired($customer)) {
            throw new EmailNotConfirmedException(__('This account isn\'t confirmed. Verify and try again.'));
        }

        $this->eventManager->dispatch('customer_data_object_login', ['customer' => $customer]);

        return $customer;
    }

    private function isConfirmationRequired(CustomerInterface $customer): bool
    {
        $customerParams = [
            (int)$customer->getWebsiteId(),
            (int)$customer->getId(),
            $customer->getEmail()
        ];

        return $customer->getConfirmation()
            && (
                $this->accountConfirmation->isConfirmationRequired(...$customerParams) ||
                $this->accountConfirmation->isEmailChangedConfirmationRequired(...$customerParams)
            );
    }
}
