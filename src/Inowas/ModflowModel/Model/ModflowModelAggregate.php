<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model;

use Inowas\Common\Boundaries\ModflowBoundary;
use Inowas\Common\Geometry\Polygon;
use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\CalculationId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\LengthUnit;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\PackageName;
use Inowas\Common\Modflow\ParameterName;
use Inowas\Common\Modflow\StressPeriods;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\Common\Soilmodel\LayerId;
use Inowas\Common\Soilmodel\Soilmodel;
use Inowas\Common\Soilmodel\SoilmodelId;
use Inowas\Common\Status\Visibility;
use Inowas\ModflowModel\Model\AMQP\FlopyCalculationResponse;
use Inowas\ModflowModel\Model\Event\ActiveCellsWereUpdated;
use Inowas\ModflowModel\Model\Event\AreaGeometryWasUpdated;
use Inowas\ModflowModel\Model\Event\BoundaryWasAdded;
use Inowas\ModflowModel\Model\Event\BoundaryWasRemoved;
use Inowas\ModflowModel\Model\Event\BoundaryWasUpdated;
use Inowas\ModflowModel\Model\Event\BoundingBoxWasChanged;
use Inowas\ModflowModel\Model\Event\CalculationIdWasChanged;
use Inowas\ModflowModel\Model\Event\CalculationWasRequested;
use Inowas\ModflowModel\Model\Event\CalculationWasFinished;
use Inowas\ModflowModel\Model\Event\CalculationWasStarted;
use Inowas\ModflowModel\Model\Event\DescriptionWasChanged;
use Inowas\ModflowModel\Model\Event\FlowPackageWasChanged;
use Inowas\ModflowModel\Model\Event\GridSizeWasChanged;
use Inowas\ModflowModel\Model\Event\LayerWasAdded;
use Inowas\ModflowModel\Model\Event\LayerWasRemoved;
use Inowas\ModflowModel\Model\Event\LayerWasUpdated;
use Inowas\ModflowModel\Model\Event\LengthUnitWasUpdated;
use Inowas\ModflowModel\Model\Event\ModflowModelWasCloned;
use Inowas\ModflowModel\Model\Event\ModflowModelWasDeleted;
use Inowas\ModflowModel\Model\Event\ModflowPackageParameterWasUpdated;
use Inowas\ModflowModel\Model\Event\ModflowPackageWasUpdated;
use Inowas\ModflowModel\Model\Event\NameWasChanged;
use Inowas\ModflowModel\Model\Event\SoilmodelMetadataWasUpdated;
use Inowas\ModflowModel\Model\Event\ModflowModelWasCreated;
use Inowas\ModflowModel\Model\Event\StressPeriodsWereUpdated;
use Inowas\ModflowModel\Model\Event\TimeUnitWasUpdated;
use Inowas\ModflowModel\Model\Event\VisibilityWasChanged;
use Inowas\ModflowModel\Model\Exception\BoundaryNotFoundInModelException;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;

class ModflowModelAggregate extends AggregateRoot
{
    /** @var  ModflowId */
    protected $modelId;

    /** @var  UserId */
    protected $userId;

    /** @var SoilmodelId */
    protected $soilmodelId;

    /** @var  CalculationId */
    protected $calculationId;

    /** @var  array */
    protected $boundaries;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ModflowId $modelId
     * @param UserId $userId
     * @param Polygon $polygon
     * @param GridSize $gridSize
     * @param BoundingBox $boundingBox
     * @return ModflowModelAggregate
     * @internal param Area $area
     */
    public static function create(
        ModflowId $modelId,
        UserId $userId,
        Polygon $polygon,
        GridSize $gridSize,
        BoundingBox $boundingBox
    ): ModflowModelAggregate
    {
        $self = new self();
        $self->modelId = $modelId;
        $self->userId = $userId;
        $self->boundaries = [];

        $self->recordThat(ModflowModelWasCreated::withParameters(
            $modelId,
            $userId,
            $polygon,
            $gridSize,
            $boundingBox
        ));

        return $self;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ModflowId $newModelId
     * @param UserId $newUserId
     * @param ModflowModelAggregate $model
     * @param bool $isTool
     * @return ModflowModelAggregate
     */
    public static function clone(
        ModflowId $newModelId,
        UserId $newUserId,
        ModflowModelAggregate $model,
        bool $isTool
    ): ModflowModelAggregate
    {
        $self = new self();
        $self->modelId = $newModelId;
        $self->userId = $newUserId;
        $self->boundaries = $model->boundaries;

        $self->recordThat(ModflowModelWasCloned::fromModelAndUserWithParameters(
            $model->modflowModelId(),
            $self->modelId,
            $self->userId,
            $model->boundaries,
            $isTool
        ));

        return $self;
    }

    public function addBoundary(UserId $userId, ModflowBoundary $boundary): void
    {
        if (in_array($boundary->boundaryId()->toString(), $this->boundaries, true)) {
            throw BoundaryNotFoundInModelException::withIds($this->modelId, $boundary->boundaryId());
        }

        $this->recordThat(BoundaryWasAdded::byUserToModel(
            $userId,
            $this->modelId,
            $boundary
        ));

        $this->boundaries[] = $boundary->boundaryId()->toString();
    }

    public function removeBoundary(UserId $userId, BoundaryId $boundaryId): void
    {
        if (in_array($boundaryId->toString(), $this->boundaries, true)){
            $this->recordThat(BoundaryWasRemoved::byUserFromModel(
                $userId,
                $this->modelId,
                $boundaryId
            ));

            unset($this->boundaries[$boundaryId->toString()]);
            return;
        }

        throw BoundaryNotFoundInModelException::withIds($this->modelId, $boundaryId);
    }

    public function updateBoundary(UserId $userId, BoundaryId $boundaryId, ModflowBoundary $boundary): void
    {
        if (in_array($boundaryId->toString(), $this->boundaries, true)){
            $this->recordThat(BoundaryWasUpdated::byUserToModel(
                $userId,
                $this->modelId,
                $boundaryId,
                $boundary
            ));

            return;
        }

        throw BoundaryNotFoundInModelException::withIds($this->modelId, $boundaryId);
    }

    public function calculationRequestWasSent(UserId $userId): void
    {
        $this->recordThat(CalculationWasRequested::withId(
            $userId,
            $this->modelId
        ));
    }

    public function calculationWasStarted(CalculationId $calculationId): void
    {
        $this->recordThat(CalculationWasStarted::withId(
            $this->modelId,
            $calculationId
        ));
    }

    public function calculationWasFinished(FlopyCalculationResponse $response): void
    {
        $this->recordThat(CalculationWasFinished::withResponse(
            $this->modelId,
            $response
        ));
    }

    public function changeDescription(UserId $userId, Description $description): void
    {
        $this->recordThat(DescriptionWasChanged::withDescription(
            $userId,
            $this->modelId,
            $description
        ));
    }

    public function changeBoundingBox(UserId $userId, BoundingBox $boundingBox): void
    {
        $this->recordThat(BoundingBoxWasChanged::withBoundingBox(
            $userId,
            $this->modelId,
            $boundingBox
        ));
    }

    public function changeVisibility(UserId $userId, Visibility $visibility): void
    {
        $this->recordThat(VisibilityWasChanged::withVisibility(
            $userId,
            $this->modelId,
            $visibility
        ));
    }

    public function changeFlowPackage(UserId $userId, PackageName $packageName): void
    {
        $this->recordThat(FlowPackageWasChanged::to(
            $userId,
            $this->modelId,
            $packageName
        ));
    }

    public function changeGridSize(UserId $userId, GridSize $gridSize): void
    {
        $this->recordThat(GridSizeWasChanged::withGridSize(
            $userId,
            $this->modelId,
            $gridSize
        ));
    }

    public function changeName(UserId $userId, Name $name): void
    {
        $this->recordThat(NameWasChanged::byUserWithName(
            $userId,
            $this->modelId,
            $name
        ));
    }

    public function delete(UserId $userId): void
    {
        $this->recordThat(ModflowModelWasDeleted::byUserWitModelId(
            $this->modelId,
            $userId
        ));
    }

    public function updateLengthUnit(UserId $userId, LengthUnit $lengthUnit): void
    {
        $this->recordThat(LengthUnitWasUpdated::withUnit(
            $userId,
            $this->modelId,
            $lengthUnit
        ));
    }

    public function updateTimeUnit(UserId $userId, TimeUnit $timeUnit): void
    {
        $this->recordThat(TimeUnitWasUpdated::withUnit(
            $userId,
            $this->modelId,
            $timeUnit
        ));
    }

    public function updateAreaGeometry(UserId $userId, Polygon $polygon): void
    {
        $this->recordThat(AreaGeometryWasUpdated::of(
            $this->modelId,
            $userId,
            $polygon
        ));
    }

    public function updateAreaActiveCells(UserId $userId, ActiveCells $activeCells): void
    {
        $this->recordThat(ActiveCellsWereUpdated::fromAreaWithIds(
            $userId,
            $this->modelId,
            $activeCells
        ));
    }

    public function updateBoundaryActiveCells(UserId $userId, BoundaryId $boundaryId, ActiveCells $activeCells): void
    {
        $this->recordThat(ActiveCellsWereUpdated::fromBoundaryWithIds(
            $userId,
            $this->modelId,
            $boundaryId,
            $activeCells
        ));
    }

    public function preprocessingWasFinished(CalculationId $calculationId): void
    {
        if ($this->calculationId->toString() !== $calculationId->toString()){
            $this->calculationId = $calculationId;
            $this->recordThat(CalculationIdWasChanged::withId($this->modelId, $calculationId));
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param PackageName $packageName
     * @param ParameterName $parameterName
     * @param $data
     */
    public function updateModflowPackageParameter(UserId $userId, PackageName $packageName, ParameterName $parameterName, $data): void
    {
        $this->recordThat(ModflowPackageParameterWasUpdated::withProps(
            $userId,
            $this->modelId,
            $packageName,
            $parameterName,
            $data
        ));
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param PackageName $packageName
     * @param $data
     */
    public function updateModflowPackage(UserId $userId, PackageName $packageName, array $data): void
    {
        $this->recordThat(ModflowPackageWasUpdated::withProps(
            $userId,
            $this->modelId,
            $packageName,
            $data
        ));
    }

    public function updateStressPeriods(UserId $userId, StressPeriods $stressPeriods): void
    {
        $this->recordThat(StressPeriodsWereUpdated::of(
            $this->modelId,
            $userId,
            $stressPeriods
        ));
    }

    /* Soilmodel-Related stuff */
    public function addLayer(UserId $userId, LayerId $id, LayerNumber $number, string $hash): void
    {
        $this->recordThat(LayerWasAdded::byUserToModel($userId, $this->modelId, $id, $number, $hash));
    }

    public function updateLayer(UserId $userId, LayerId $layerId, LayerId $newLayerId, LayerNumber $layerNumber, string $hash): void
    {
        $this->recordThat(LayerWasUpdated::byUserToModel($userId, $this->modelId, $layerId, $newLayerId, $layerNumber, $hash));
    }

    public function removeLayer(UserId $userId, LayerId $layerId): void
    {
        $this->recordThat(LayerWasRemoved::byUserToModel($userId, $this->modelId, $layerId));
    }

    public function updateSoilmodelMetadata(UserId $userId, Soilmodel $soilmodel): void
    {
        $this->recordThat(SoilmodelMetadataWasUpdated::byUserToModel($userId, $this->modelId, $soilmodel));
    }

    public function modflowModelId(): ModflowId
    {
        return $this->modelId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function soilmodelId(): SoilmodelId
    {
        return $this->soilmodelId;
    }

    public function calculationId(): CalculationId
    {
        return $this->calculationId;
    }

    protected function whenActiveCellsWereUpdated(ActiveCellsWereUpdated $event): void
    {}

    protected function whenAreaGeometryWasUpdated(AreaGeometryWasUpdated $event): void
    {}

    protected function whenBoundaryWasAdded(BoundaryWasAdded $event): void
    {
        $this->boundaries[] = $event->boundary()->boundaryId()->toString();
    }

    protected function whenBoundaryWasUpdated(BoundaryWasUpdated $event): void
    {}

    protected function whenBoundaryWasRemoved(BoundaryWasRemoved $event): void
    {
        unset($this->boundaries[$event->boundaryId()->toString()]);
    }

    protected function whenBoundingBoxWasChanged(BoundingBoxWasChanged $event): void
    {}

    protected function whenCalculationIdWasChanged(CalculationIdWasChanged $event): void
    {
        $this->calculationId = $event->calculationId();
    }

    protected function whenCalculationWasRequested(CalculationWasRequested $event): void
    {}

    protected function whenCalculationWasFinished(CalculationWasFinished $event): void
    {}

    protected function whenCalculationWasStarted(CalculationWasStarted $event): void
    {}

    protected function whenDescriptionWasChanged(DescriptionWasChanged $event): void
    {}

    protected function whenFlowPackageWasChanged(FlowPackageWasChanged $event): void
    {}

    protected function whenGridSizeWasChanged(GridSizeWasChanged $event): void
    {}

    protected function whenLayerWasAdded(LayerWasAdded $event): void {}

    protected function whenLayerWasRemoved(LayerWasRemoved $event): void {}

    protected function whenLayerWasUpdated(LayerWasUpdated $event): void {}

    protected function whenLengthUnitWasUpdated(LengthUnitWasUpdated $event): void
    {}

    protected function whenModflowModelWasCloned(ModflowModelWasCloned $event): void
    {
        $this->modelId = $event->modelId();
        $this->userId = $event->userId();
        $this->calculationId = CalculationId::fromString('');
        $this->boundaries = $event->boundaries();
    }

    protected function whenModflowModelWasCreated(ModflowModelWasCreated $event): void
    {
        $this->modelId = $event->modelId();
        $this->userId = $event->userId();
        $this->calculationId = CalculationId::fromString('');
        $this->boundaries = [];
    }

    protected function whenModflowModelWasDeleted(ModflowModelWasDeleted $event): void
    {}

    protected function whenModflowPackageParameterWasUpdated(ModflowPackageParameterWasUpdated $event): void
    {}

    protected function whenModflowPackageWasUpdated(ModflowPackageWasUpdated $event): void
    {}

    protected function whenNameWasChanged(NameWasChanged $event): void
    {}

    protected function whenSoilmodelMetadataWasUpdated(SoilmodelMetadataWasUpdated $event): void
    {}

    protected function whenStressPeriodsWereUpdated(StressPeriodsWereUpdated $event): void
    {}

    protected function whenTimeUnitWasUpdated(TimeUnitWasUpdated $event): void
    {}

    protected function whenVisibilityWasChanged(VisibilityWasChanged $event): void
    {}

    protected function aggregateId(): string
    {
        return $this->modelId->toString();
    }

    protected function apply(AggregateChanged $e): void
    {
        $handler = $this->determineEventHandlerMethodFor($e);
        if (! method_exists($this, $handler)) {
            throw new \RuntimeException(sprintf(
                'Missing event handler method %s for aggregate root %s',
                $handler,
                get_class($this)
            ));
        }
        $this->{$handler}($e);
    }

    protected function determineEventHandlerMethodFor(AggregateChanged $e)
    {
        return 'when' . implode(array_slice(explode('\\', get_class($e)), -1));
    }
}
