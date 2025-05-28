<?php

namespace App\Serializer\Normalizer;

use App\Entity\Village;
use App\Service\ResourceService;
use DateTimeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class VillageNormalizer implements NormalizerInterface
{
    public function __construct(private readonly ResourceService $resourceService)
    {
    }

    /**
     * @param Village $data
     */
    public function normalize($data, ?string $format = null, array $context = []): array
    {
        $troops            = 0;
        $troopAttackPower  = 0;
        $troopDefensePower = 0;
        foreach ($data->getTroops() as $troop) {
            if (!$troop->getAmount()) {
                continue;
            }

            $troops            += $troop->getAmount();
            $troopAttackPower  += $troop->getPower()->getAttack() * $troop->getAmount();
            $troopDefensePower += $troop->getPower()->getDefense() * $troop->getAmount();
        }
        return [
            'id'        => $data->getId(),
            'name'      => $data->getName(),
            'x'         => $data->getX(),
            'y'         => $data->getY(),
            'level'     => $data->getLevel(),
            'caps'      => [
                'storage'    => $this->resourceService->getWarehouseCap($data),
                'population' => $this->resourceService->getFarmCap($data),
            ],
            'resources' => array_map(fn($resource) => [
                'category' => $resource->getCategory()->value(),
                'pph'      => $this->resourceService->getProductionPerHour($resource->getResponsibleBuilding()),
                'amount'   => $resource->getAmount(),
            ], $data->getResources()->toArray()),
            'buildings' => array_map(fn($building) => [
                'category'        => $building->getCategory()->value(),
                'level'           => $building->getLevel(),
                'upgradeFinishAt' => $building->getUpgradeFinishAt()?->format(DATE_ATOM),
            ], $data->getBuildings()->toArray()),
            'troops'    => array_map(fn($troop) => [
                'role'             => $troop->getRole()->value(),
                'amount'           => $troop->getAmount(),
                'trainingFinishAt' => $troop->getTrainingFinishAt()?->format(DATE_ATOM),
                'queue'            => $troop->getTrainingQueue(),
            ], $data->getTroops()->toArray()),
            'player'    => [
                'id'    => $data->getPlayer()?->getId(),
                'email' => $data->getPlayer()?->getEmail()
            ],
            'meta'      => [
                'isHuman'   => $data->isHuman(),
                'createdAt' => $data->getCreatedAt()->format(DateTimeInterface::ATOM),
            ],
            'counters'  => [
                'population'        => $troops,
                'troopAttackPower'  => $troopAttackPower,
                'troopDefensePower' => $troopDefensePower,
                'incomingAttacks'   => mt_rand(0, 3)
            ]
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Village;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Village::class => true];
    }
}
