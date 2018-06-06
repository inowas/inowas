<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Service;

use Inowas\AppBundle\Model\UserPermission;
use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Grid\AffectedCells;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\HeadObservationCollection;
use Inowas\Common\Modflow\LengthUnit;
use Inowas\Common\Modflow\ModflowModel;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Modflow\StressPeriods;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\Common\Status\Visibility;
use Inowas\GeoTools\Service\GeoTools;
use Inowas\ModflowModel\Infrastructure\Projection\ActiveCells\ActiveCellsFinder;
use Inowas\ModflowModel\Infrastructure\Projection\ModelList\ModelFinder;
use Inowas\ModflowModel\Model\Packages\ChdStressPeriodData;
use Inowas\ModflowModel\Model\Packages\GhbStressPeriodData;
use Inowas\ModflowModel\Model\Packages\RchStressPeriodData;
use Inowas\ModflowModel\Model\Packages\RivStressPeriodData;
use Inowas\ModflowModel\Model\Packages\WelStressPeriodData;
use Inowas\ScenarioAnalysis\Infrastructure\Projection\ScenarioAnalysisFinder;

class ModflowModelManager
{

    /** @var  ActiveCellsFinder */
    protected $activeCellsFinder;

    /** @var  BoundaryManager */
    protected $boundaryManager;

    /** @var  GeoTools */
    protected $geoTools;

    /** @var  ModelFinder */
    protected $modelFinder;

    /** @var  ScenarioAnalysisFinder */
    protected $scenarioAnalysisFinder;

    /** @var  StressPeriodDataGenerator */
    protected $stressPeriodDataGenerator;


    /**
     * ModflowModelManager constructor.
     * @param ActiveCellsFinder $activeCellsFinder
     * @param BoundaryManager $boundaryManager
     * @param GeoTools $geoTools
     * @param ModelFinder $modelFinder
     * @param ScenarioAnalysisFinder $scenarioAnalysisFinder
     * @param StressPeriodDataGenerator $stressPeriodDataGenerator
     */
    public function __construct(
        ActiveCellsFinder $activeCellsFinder,
        BoundaryManager $boundaryManager,
        GeoTools $geoTools,
        ModelFinder $modelFinder,
        ScenarioAnalysisFinder $scenarioAnalysisFinder,
        StressPeriodDataGenerator $stressPeriodDataGenerator
    ){
        $this->activeCellsFinder = $activeCellsFinder;
        $this->boundaryManager = $boundaryManager;
        $this->geoTools = $geoTools;
        $this->modelFinder = $modelFinder;
        $this->scenarioAnalysisFinder = $scenarioAnalysisFinder;
        $this->stressPeriodDataGenerator = $stressPeriodDataGenerator;
    }

    /**
     * @param ModflowId $modelId
     * @param UserId $userId
     * @return ModflowModel|null
     * @throws \Exception
     */
    public function findModel(ModflowId $modelId, UserId $userId): ?ModflowModel
    {
        $model = $this->modelFinder->findById($modelId);

        if (null === $model) {
            return null;
        }

        $visibility = Visibility::private();

        $permission = UserPermission::noPermission();
        if ($model['public']) {
            $permission = UserPermission::readOnly();
            $visibility = Visibility::public();
        }

        if ($userId->toString() === $model['user_id']) {
            $permission = UserPermission::readWriteScenario();
            if (! $this->scenarioAnalysisFinder->isScenario($modelId)) {
                $permission = UserPermission::readWriteBaseModel();
            }
        }

        return ModflowModel::fromParams(
            $modelId,
            Name::fromString($model['name']),
            Description::fromString($model['description']),
            Geometry::fromJson($model['area'])->value(),
            BoundingBox::fromArray(json_decode($model['bounding_box'], true)),
            GridSize::fromArray((array)json_decode($model['grid_size'])),
            TimeUnit::fromInt($model['time_unit']),
            LengthUnit::fromInt($model['length_unit']),
            !empty($model['active_cells']) ?
                ActiveCells::fromArray(json_decode($model['active_cells'], true))
                : $this->getAreaActiveCells($modelId),
            $permission,
            $visibility
        );
    }

    /**
     * @param ModflowId $modflowId
     * @return UserId|null
     */
    public function getUserId(ModflowId $modflowId): ?UserId
    {
        return $this->modelFinder->getUserId($modflowId);
    }

    /**
     * @param ModflowId $modelId
     * @return array
     */
    public function findBoundaries(ModflowId $modelId): array
    {
        return $this->boundaryManager->findBoundariesByModelId($modelId);
    }

    /**
     * @param ModflowId $modflowId
     * @return TimeUnit
     */
    public function getTimeUnitByModelId(ModflowId $modflowId): TimeUnit
    {
        return $this->modelFinder->getTimeUnitByModelId($modflowId);
    }

    /**
     * @param ModflowId $modflowId
     * @return LengthUnit
     */
    public function getLengthUnitByModelId(ModflowId $modflowId): LengthUnit
    {
        return $this->modelFinder->getLengthUnitByModelId($modflowId);
    }

    /**
     * @param ModflowId $modflowId
     * @return StressPeriods
     */
    public function getStressPeriodsByModelId(ModflowId $modflowId): StressPeriods
    {
        return $this->modelFinder->getStressPeriodsByModelId($modflowId);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ModflowId $modflowId
     * @param DateTime $start
     * @param DateTime $end
     * @param TimeUnit $timeUnit
     * @return StressPeriods
     * @throws \Inowas\ModflowModel\Model\Exception\SqlQueryException
     * @throws \Exception
     */
    public function calculateStressPeriods(ModflowId $modflowId, DateTime $start, DateTime $end, TimeUnit $timeUnit): StressPeriods
    {
        /** @var DateTime[] $bcDates */
        $dates = $this->boundaryManager->findStressPeriodDatesById($modflowId);
        return StressPeriods::createFromDates($dates, $start, $end, $timeUnit);
    }

    /**
     * @param ModflowId $modelId
     * @return ActiveCells
     * @throws \exception
     */
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

    /**
     * @param ModflowId $modelId
     * @param BoundaryId $boundaryId
     * @return AffectedCells
     * @throws \Exception
     */
    public function getBoundaryAffectedCells(ModflowId $modelId, BoundaryId $boundaryId): AffectedCells
    {
        $boundary = $this->boundaryManager->getBoundary($modelId, $boundaryId);
        return $boundary->affectedCells();
    }

    /**
     * @param ModflowId $modflowId
     * @return BoundingBox
     */
    public function getBoundingBox(ModflowId $modflowId): BoundingBox
    {
        return $this->modelFinder->getBoundingBoxByModflowModelId($modflowId);
    }

    /**
     * @param ModflowId $modflowId
     * @return GridSize
     * @throws \Exception
     */
    public function getGridSize(ModflowId $modflowId): GridSize
    {
        return $this->modelFinder->getGridSizeByModflowModelId($modflowId);
    }

    /**
     * @param ModflowId $modflowId
     * @param string $type
     * @return int
     * @throws \Inowas\ModflowModel\Model\Exception\SqlQueryException
     * @throws \Inowas\Common\Exception\InvalidTypeException
     */
    public function countModelBoundaries(ModflowId $modflowId, string $type): int
    {
        return $this->boundaryManager->getNumberOfModelBoundariesByType($modflowId, BoundaryType::fromString($type));
    }

    /**
     * @param ModflowId $modflowId
     * @param StressPeriods $stressPeriods
     * @return ChdStressPeriodData
     * @throws \Exception
     */
    public function generateChdStressPeriodData(ModflowId $modflowId, StressPeriods $stressPeriods): ChdStressPeriodData
    {
        $gridSize = $this->getGridSize($modflowId);
        $boundingBox = $this->getBoundingBox($modflowId);

        return $this->stressPeriodDataGenerator->fromConstantHeadBoundaries(
            $this->boundaryManager->findConstantHeadBoundaries($modflowId),
            $stressPeriods,
            $gridSize,
            $boundingBox
        );
    }

    /**
     * @param ModflowId $modflowId
     * @param StressPeriods $stressPeriods
     * @return GhbStressPeriodData
     * @throws \Exception
     */
    public function generateGhbStressPeriodData(ModflowId $modflowId, StressPeriods $stressPeriods): GhbStressPeriodData
    {
        $gridSize = $this->getGridSize($modflowId);
        $boundingBox = $this->getBoundingBox($modflowId);

        return $this->stressPeriodDataGenerator->fromGeneralHeadBoundaries(
            $this->boundaryManager->findGeneralHeadBoundaries($modflowId),
            $stressPeriods,
            $gridSize,
            $boundingBox
        );
    }

    /**
     * @param ModflowId $modflowId
     * @param StressPeriods $stressPeriods
     * @return RchStressPeriodData
     * @throws \Exception
     */
    public function generateRchStressPeriodData(ModflowId $modflowId, StressPeriods $stressPeriods): RchStressPeriodData
    {
        $gridSize = $this->getGridSize($modflowId);

        return $this->stressPeriodDataGenerator->fromRechargeBoundaries(
            $this->boundaryManager->findRechargeBoundaries($modflowId),
            $stressPeriods,
            $gridSize
        );
    }

    /**
     * @param ModflowId $modflowId
     * @param StressPeriods $stressPeriods
     * @return RivStressPeriodData
     * @throws \Exception
     */
    public function generateRivStressPeriodData(ModflowId $modflowId, StressPeriods $stressPeriods): RivStressPeriodData
    {
        $gridSize = $this->getGridSize($modflowId);
        $boundingBox = $this->getBoundingBox($modflowId);

        return $this->stressPeriodDataGenerator->fromRiverBoundaries(
            $this->boundaryManager->findRiverBoundaries($modflowId),
            $stressPeriods,
            $gridSize,
            $boundingBox
        );
    }

    /**
     * @param ModflowId $modflowId
     * @param StressPeriods $stressPeriods
     * @return WelStressPeriodData
     * @throws \Inowas\Common\Exception\InvalidTypeException
     * @throws \Exception
     */
    public function generateWelStressPeriodData(ModflowId $modflowId, StressPeriods $stressPeriods): WelStressPeriodData
    {
        return $this->stressPeriodDataGenerator->fromWellBoundaries(
            $this->boundaryManager->findWellBoundaries($modflowId),
            $stressPeriods
        );
    }


    /**
     * @param ModflowId $modflowId
     * @param StressPeriods $stressPeriods
     * @return HeadObservationCollection
     * @throws \Inowas\Common\Exception\InvalidTypeException
     * @throws \Exception
     */
    public function generateHobData(ModflowId $modflowId, StressPeriods $stressPeriods): HeadObservationCollection
    {
        return $this->stressPeriodDataGenerator->fromHeadObservationWells(
            $this->boundaryManager->findHeadObservationWells($modflowId),
            $stressPeriods
        );
    }

    /**
     * @param ModflowId $modelId
     * @return ActiveCells
     * @throws \exception
     */
    private function calculateAreaActiveCells(ModflowId $modelId): ActiveCells
    {
        $affectedLayers = AffectedLayers::fromArray([0]);
        $boundingBox = $this->modelFinder->getBoundingBoxByModflowModelId($modelId);
        $polygon = $this->modelFinder->getAreaPolygonByModflowModelId($modelId);
        $geometry = Geometry::fromPolygon($polygon);
        $gridSize = $this->modelFinder->getGridSizeByModflowModelId($modelId);
        return $this->geoTools->calculateActiveCellsFromGeometryAndAffectedLayers($geometry, $affectedLayers, $boundingBox, $gridSize);
    }
}
