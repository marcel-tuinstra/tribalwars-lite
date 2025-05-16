<?php

namespace App\Serializer\Normalizer;

use App\Entity\Player;
use DateTimeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PlayerNormalizer implements NormalizerInterface
{
    /**
     * @param Player $data
     */
    public function normalize($data, ?string $format = null, array $context = []): array
    {
        return [
            'id'       => $data->getId(),
            'email'    => $data->getEmail(),
            'meta'     => [
                'createdAt' => $data->getCreatedAt()->format(DateTimeInterface::ATOM),
            ],
            'counters' => [
                'villages' => [
                    'total' => $data->getVillages()->count()
                ],
            ]
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Player;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Player::class => true];
    }
}
