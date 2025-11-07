<?php
declare(strict_types=1);

namespace Novikor\Telemage\Api\Customer;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\EmailNotConfirmedException;

interface CreateCustomerTokenByJWEInterface
{
    /**
     * @param string $jwe
     * @return string
     * @throws AuthenticationException
     * @throws EmailNotConfirmedException
     */
    public function execute(string $jwe): string;
}
