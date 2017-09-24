<?php

namespace Inowas\ModflowModel\Service;

use Inowas\Common\Boundaries\BoundaryList;
use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\Boundaries\ModflowBoundary;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Modflow\Name;
use Inowas\ModflowModel\Infrastructure\Projection\BoundaryList\BoundaryFinder;


class BoundaryManager
{
    /** @var BoundaryFinder */
    private $boundaryFinder;

    public function __construct(BoundaryFinder $boundaryFinder)
    {
        $this->boundaryFinder = $boundaryFinder;
    }

    public function getTotalNumberOfModelBoundaries(ModflowId $modelId): int
    {
        return $this->boundaryFinder->getTotalNumberOfModelBoundaries($modelId);
    }

    public function getNumberOfModelBoundariesByType(ModflowId $modelId, BoundaryType $type): int
    {
        return $this->boundaryFinder->getNumberOfModelBoundariesByType($modelId, $type);
    }

    public function findConstantHeadBoundaries(ModflowId $modelId): array
    {
        return $this->boundaryFinder->findConstantHeadBoundaries($modelId);
    }

    public function findGeneralHeadBoundaries(ModflowId $modelId): array
    {
        return $this->boundaryFinder->findGeneralHeadBoundaries($modelId);
    }

    public function findRechargeBoundaries(ModflowId $modelId): array
    {
        return $this->boundaryFinder->findRechargeBoundaries($modelId);
    }

    public function findRiverBoundaries(ModflowId $modelId): array
    {
        return $this->boundaryFinder->findRiverBoundaries($modelId);
    }

    public function findWellBoundaries(ModflowId $modelId): array
    {
        return $this->boundaryFinder->findWellBoundaries($modelId);
    }

    public function findBoundariesByModelId(ModflowId $modelId): array
    {
        return $this->boundaryFinder->findBoundariesByModelId($modelId);
    }

    public function getBoundary(ModflowId $modelId, BoundaryId $boundaryId): ?ModflowBoundary
    {
        return $this->boundaryFinder->getBoundary($modelId, $boundaryId);
    }

    public function getBoundaryName(ModflowId $modelId, BoundaryId $boundaryId): ?Name
    {
        return $this->boundaryFinder->getBoundaryName($modelId, $boundaryId);
    }

    public function getBoundaryGeometry(ModflowId $modelId, BoundaryId $boundaryId): ?Geometry
    {
        return $this->boundaryFinder->getBoundaryGeometry($modelId, $boundaryId);
    }

    public function getBoundaryType(ModflowId $modelId, BoundaryId $boundaryId): ?BoundaryType
    {
        return $this->boundaryFinder->getBoundaryType($modelId, $boundaryId);
    }

    public function findStressPeriodDatesById(ModflowId $modelId): array
    {
        return $this->boundaryFinder->findStressPeriodDatesById($modelId);
    }

    public function getAffectedLayersByModelAndBoundary(ModflowId $modelId, BoundaryId $boundaryId): AffectedLayers
    {
        return $this->boundaryFinder->getAffectedLayersByModelAndBoundary($modelId, $boundaryId);
    }

    public function getBoundaryList(ModflowId $modelId): BoundaryList
    {
        return $this->boundaryFinder->getBoundaryList($modelId);
    }
}
