<?php

declare(strict_types=1);

namespace Novikor\Telemage\Model;

interface ConfigInterface
{
    public function isEnabled(int|string|null $websiteId = null): bool;

    public function getBotToken(int|string|null $websiteId = null): ?string;

    public function getJweSecret(int|string|null $websiteId = null): ?string;
}
