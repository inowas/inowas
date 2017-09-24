<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Service;

use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ModflowId;
use Inowas\GeoTools\Service\GeoTools;
use Inowas\ModflowModel\Infrastructure\Projection\ActiveCells\ActiveCellsFinder;
use Inowas\ModflowModel\Infrastructure\Projection\ModelList\ModelFinder;


class ActiveCellsManager
{

    /** @var  ActiveCellsFinder */
    protected $activeCellsFinder;

    /** @var  BoundaryManager */
    protected $boundaryManager;

    /** @var  GeoTools */
    protected $geoTools;

    /** @var  ModelFinder */
    protected $modelFinder;


    public function __construct(
        ActiveCellsFinder $activeCellsFinder,
        BoundaryManager $boundaryManager,
        GeoTools $geoTools,
        ModelFinder $modelFinder
    ){
        $this->activeCellsFinder = $activeCellsFinder;
        $this->boundaryManager = $boundaryManager;
        $this->geoTools = $geoTools;
        $this->modelFinder = $modelFinder;
    }


    public function getAreaActiveCells(ModflowId $modelId): ActiveCells
    {
        $activeCells = $this->activeCellsFinder->findAreaActiveCells($modelId);
        if ($activeCells instanceof ActiveCells) {
            return $activeCells;
        }

        $activeCells = $this->calculateAreaActiveCells($modelId);
        $this->activeCellsFinder->updateAreaActiveCells($modelId, $activeCells);
        return $activeCells;
    }

    public function getBoundaryActiveCells(ModflowId $modelId, BoundaryId $boundaryId): ActiveCells
    {
        $activeCells = $this->activeCellsFinder->findBoundaryActiveCells($modelId, $boundaryId);
        if ($activeCells instanceof ActiveCells) {
            return $activeCells;
        }

        $activeCells = $this->calculateBoundaryActiveCells($modelId, $boundaryId);
        $this->activeCellsFinder->updateBoundaryActiveCells($modelId, $boundaryId, $activeCells);
        return $activeCells;
    }

    private function calculateAreaActiveCells(ModflowId $modelId): ActiveCells
    {
        $affectedLayers = AffectedLayers::fromArray([0]);
        $boundingBox = $this->modelFinder->getBoundingBoxByModflowModelId($modelId);
        $polygon = $this->modelFinder->getAreaPolygonByModflowModelId($modelId);
        $geometry = Geometry::fromPolygon($polygon);
        $gridSize = $this->modelFinder->getGridSizeByModflowModelId($modelId);
        return $this->geoTools->calculateActiveCellsFromGeometryAndAffectedLayers($geometry, $affectedLayers, $boundingBox, $gridSize);
    }

    private function calculateBoundaryActiveCells(ModflowId $modelId, BoundaryId $boundaryId): ActiveCells
    {
        $affectedLayers = $this->boundaryManager->getAffectedLayersByModelAndBoundary($modelId, $boundaryId);
        $boundingBox = $this->modelFinder->getBoundingBoxByModflowModelId($modelId);
        $geometry = $this->boundaryManager->getBoundaryGeometry($modelId, $boundaryId);
        $gridSize = $this->modelFinder->getGridSizeByModflowModelId($modelId);
        return $this->geoTools->calculateActiveCellsFromGeometryAndAffectedLayers($geometry, $affectedLayers, $boundingBox, $gridSize);
    }
}
