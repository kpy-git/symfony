<?php

namespace App\Shared\Domain\Service\Denormalizer;

use App\Shared\Domain\ValueObject\ProductCode;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductCodeDenormalizer implements DenormalizerInterface
{

    /**
     * @inheritDoc
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return ProductCode::fromSKU((string)$data);
    }

    /**
     * @inheritDoc
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === ProductCode::class;
    }

    /**
     * @inheritDoc
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            ProductCode::class => true
        ];
    }
}
