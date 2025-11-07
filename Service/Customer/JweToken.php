<?php

declare(strict_types=1);

namespace Novikor\Telemage\Service\Customer;

use Exception;
use Jose\Component\Checker\ClaimCheckerManager;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Checker\NotBeforeChecker;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Algorithm\ContentEncryption\A256GCM;
use Jose\Component\Encryption\Algorithm\KeyEncryption\Dir;
use Jose\Component\Encryption\JWEBuilder;
use Jose\Component\Encryption\JWEDecrypter;
use Jose\Component\Encryption\JWELoader;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\Encryption\Serializer\JWESerializerManager;
use Jose\Component\KeyManagement\JWKFactory;
use Magento\Framework\Jwt\Exception\JwtException;
use Novikor\Telemage\Model\ConfigInterface;
use Novikor\Telemage\Service\Psr\Clock;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
readonly class JweToken
{
    private const int LIFETIME = 5 * 60;

    public function __construct(
        private ConfigInterface $config,
        private Clock $clock,
        private LoggerInterface $logger
    ) {
    }

    public function generateForCustomer(int $customerId): string
    {
        $key = $this->getJWK();

        [$keyEncryptionAlgorithmManager, $contentEncryptionAlgorithmManager] = $this->getAlgorithmManagers();

        $jweBuilder = new JWEBuilder(
            $keyEncryptionAlgorithmManager,
            $contentEncryptionAlgorithmManager,
        );

        $now = time();
        $payload = json_encode([
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + self::LIFETIME,
            'customer_id' => $customerId,
        ]);

        $jwe = $jweBuilder
            ->create()
            ->withPayload($payload)
            ->withSharedProtectedHeader([
                'alg' => 'dir',
                'enc' => 'A256GCM',
            ])
            ->addRecipient($key)
            ->build();

        $serializer = new CompactSerializer();
        return $serializer->serialize($jwe, 0);
    }

    public function validateAndGetCustomerId(string $token): int
    {
        $jwk = $this->getJWK();
        [$keyEncryptionAlgorithmManager, $contentEncryptionAlgorithmManager] = $this->getAlgorithmManagers();

        $decrypter = new JWEDecrypter(
            $keyEncryptionAlgorithmManager,
            $contentEncryptionAlgorithmManager,
        );
        $serializerManager = new JWESerializerManager([
            new CompactSerializer(),
        ]);

        $jweLoader = new JWELoader(
            serializerManager: $serializerManager,
            jweDecrypter: $decrypter,
            headerCheckerManager: null
        );
        try {
            $decryptedRecipient = null;
            $jwe = $jweLoader->loadAndDecryptWithKey($token, $jwk, $decryptedRecipient);
            $payload = json_decode((string)$jwe->getPayload(), true);
            $claimCheckerManager = new ClaimCheckerManager([
                new IssuedAtChecker(clock: $this->clock),
                new NotBeforeChecker(clock: $this->clock),
                new ExpirationTimeChecker(clock: $this->clock),
            ]);
            $claimCheckerManager->check($payload);
        } catch (Exception $e) {
            $this->logger->error('JWE decoding failed', [
                'error' => $e,
            ]);
            throw new JwtException('JWE Decoding Failure: unable to decode or verify token', $e->getCode(), $e);
        }

        if (empty($payload['customer_id'])) {
            throw new JwtException('JWE Decoding Failure: missing customer_id');
        }

        return (int)$payload['customer_id'];
    }

    private function getJWK(): JWK
    {
        return JWKFactory::createFromSecret($this->config->getJweSecret(), ['alg' => 'A256GCM', 'use' => 'enc']);
    }

    private function getAlgorithmManagers(): array
    {
        return [
            new AlgorithmManager([new Dir()]),
            new AlgorithmManager([new A256GCM()])
        ];
    }
}
