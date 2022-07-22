<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\DTO\LogInput;
use App\Entity\Log;
use App\Serializer\Encoder\LogDecoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class LogNormalizer implements DenormalizerInterface, CacheableSupportsMethodInterface
{
    private $objectNormalizer;

    public function __construct(ObjectNormalizer $objectNormalizer)
    {
        $this->objectNormalizer = $objectNormalizer;
    }


    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $this->adjustData($data);
        $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $this->createDto($context);
        return $this->objectNormalizer->denormalize($data, $type, $format, $context);
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return $type === Log::class;
    }

    private function createDto(array $context): LogInput
    {
        $entity = $context['object_to_populate'] ?? null;

        if ($entity && !$entity instanceof Log) {
            throw new \Exception(sprintf('Unexpected resource class "%s"', get_class($entity)));
        }

        return LogInput::createFromEntity($entity);
    }

    /**
     * status code comes as string we should convert it to int this function handle this
     */
    private function adjustData(&$data)
    {
        if (is_array($data) && array_key_exists(LogDecoder::STATUS_CODE_KEY, $data)) {
            $data[LogDecoder::STATUS_CODE_KEY] = (int)$data[LogDecoder::STATUS_CODE_KEY];
        }
    }


}
