<?php
declare(strict_types=1);

namespace Novikor\Telemage\Service\TelemageAPI;

use Magento\Framework\Exception\ConfigurationMismatchException;
use Novikor\Telemage\Service\TelemageAPI\SDK\Client;

interface ApiClientFlyweightFactoryInterface
{
    /**
     * @throws ConfigurationMismatchException
     */
    public function get(int|string $websiteId): Client;
}
