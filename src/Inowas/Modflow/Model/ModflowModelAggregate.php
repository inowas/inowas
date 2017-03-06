<?php

declare(strict_types=1);

namespace Inowas\Modflow\Model;

use Inowas\Common\Boundaries\AbstractBoundary;
use Inowas\Common\Boundaries\AreaBoundary;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Boundaries\ModflowBoundary;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\IdInterface;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\SoilModelId;
use Inowas\Common\Id\UserId;
use Inowas\Modflow\Model\Event\BoundaryWasAdded;
use Inowas\Modflow\Model\Event\BoundaryWasAddedToScenario;
use Inowas\Modflow\Model\Event\BoundaryWasRemoved;
use Inowas\Modflow\Model\Event\BoundaryWasRemovedFromScenario;
use Inowas\Modflow\Model\Event\ModflowModelBoundaryWasUpdated;
use Inowas\Modflow\Model\Event\ModflowModelBoundingBoxWasChanged;
use Inowas\Modflow\Model\Event\ModflowModelDescriptionWasChanged;
use Inowas\Modflow\Model\Event\ModflowModelGridSizeWasChanged;
use Inowas\Modflow\Model\Event\ModflowModelNameWasChanged;
use Inowas\Modflow\Model\Event\ModflowModelSoilModelIdWasChanged;
use Inowas\Modflow\Model\Event\ModflowModelWasCreated;
use Inowas\Modflow\Model\Event\ModflowScenarioDescriptionWasChanged;
use Inowas\Modflow\Model\Event\ModflowScenarioNameWasChanged;
use Inowas\Modflow\Model\Event\ModflowScenarioWasAdded;
use Inowas\Modflow\Model\Event\ModflowScenarioWasRemoved;
use Inowas\Modflow\Model\Event\ScenarioBoundaryWasUpdated;
use Prooph\EventSourcing\AggregateRoot;

class ModflowModelAggregate extends AggregateRoot
{
    /** @var  ModflowId */
    protected $modflowId;

    /** @var  ModflowId */
    protected $baseModelId;

    /** @var  UserId */
    protected $owner;

    /** @var  bool */
    protected $public;

    /** @var ModflowModelName */
    protected $name;

    /** @var ModflowModelDescription */
    protected $description;

    /** @var GridSize */
    protected $gridSize;

    /** @var BoundingBox  */
    protected $boundingBox;

    /** @var AbstractBoundary */
    protected $area;

    /** @var SoilModelId */
    protected $soilmodelId;

    /** @var array  */
    protected $boundaries;

    /** @var array */
    protected $scenarios;

    #/** @var  \DateTime */
    #protected $start;

    #/** @var  \DateTime */
    #protected $end;

    #/** @var TimeUnit */
    #protected $timeUnit;

    public static function create(UserId $userId, ModflowId $modflowId): ModflowModelAggregate
    {
        $self = new self();
        $self->modflowId = $modflowId;
        $self->owner = $userId;
        $self->scenarios = [];
        $self->boundaries = [];

        $self->recordThat(ModflowModelWasCreated::byUserWithModflowId($userId, $modflowId));
        return $self;
    }

    public function createCalculationFromBaseModel(ModflowId $calculationId, DateTime $start, DateTime $end): ModflowCalculationAggregate
    {
        return ModflowCalculationAggregate::create(
            $calculationId,
            $this->modflowId,
            $this->soilmodelId,
            $this->owner,
            $this->gridSize,
            $start,
            $end
        );
    }

    public function createCalculationFromScenario(ModflowId $calculationId, ModflowId $scenarioId, DateTime $start, DateTime $end): ?ModflowCalculationAggregate
    {
        if ($this->contains($scenarioId, $this->scenarios)) {
            return ModflowCalculationAggregate::create(
                $calculationId,
                $scenarioId,
                $this->soilmodelId,
                $this->owner,
                $this->gridSize,
                $start,
                $end
            );
        }

        return null;
    }

    public function addScenario(UserId $userId, ModflowId $scenarioId): void
    {
        /** @var ModflowModelAggregate $scenario */
        $scenario = $this->createScenarioFromThis($userId, $scenarioId);

        if (! $this->contains($scenario->modflowId, $this->scenarios)){
            $this->scenarios[$scenarioId->toString()] = $scenario;
            $this->recordThat(ModflowScenarioWasAdded::withId($userId, $this->modflowId, $scenarioId));
        }
    }

    public function removeScenario(UserId $userId, ModflowId $scenarioId): void
    {
        if ( $this->contains($scenarioId, $this->scenarios)) {
            unset($this->scenarios[$scenarioId->toString()]);
            $this->recordThat(ModflowScenarioWasRemoved::from($userId, $this->modflowId, $scenarioId));
        }
    }

    public function changeName(ModflowModelName $name): void
    {
        $this->name = $name;
    }

    public function changeBaseModelName(UserId $userId, ModflowModelName $name)
    {
        $this->name = $name;
        $this->recordThat(ModflowModelNameWasChanged::byUserWithName(
            $userId,
            $this->modflowId,
            $this->name
        ));
    }

    public function changeScenarioName(UserId $userId, ModflowId $scenarioId, ModflowModelName $name)
    {
        if ($this->contains($scenarioId, $this->scenarios)) {
            /** @var ModflowModelAggregate $scenario */
            $scenario = $this->scenarios[$scenarioId->toString()];
            $scenario->changeName($name);
            $this->recordThat(ModflowScenarioNameWasChanged::byUserWithName(
                $userId,
                $this->modflowId,
                $scenarioId,
                $scenario->name()
            ));
        }
    }

    public function changeDescription(ModflowModelDescription $description): void
    {
        $this->description = $description;
    }

    public function changeBaseModelDescription(UserId $userId, ModflowModelDescription $description)
    {
        $this->description = $description;
        $this->recordThat(ModflowModelDescriptionWasChanged::withDescription(
            $userId,
            $this->modflowId,
            $this->description)
        );
    }

    public function changeScenarioDescription(UserId $userId, ModflowId $scenarioId, ModflowModelDescription $description)
    {
        if ($this->contains($scenarioId, $this->scenarios)) {
            /** @var ModflowModelAggregate $scenario */
            $scenario = $this->scenarios[$scenarioId->toString()];
            $scenario->changeDescription($description);
            $this->recordThat(ModflowScenarioDescriptionWasChanged::byUserWithName(
                $userId,
                $this->modflowId,
                $scenarioId,
                $scenario->description()
            ));
        }
    }

    public function changeGridSize(UserId $userId, GridSize $gridSize)
    {
        $this->gridSize = $gridSize;
        $this->recordThat(ModflowModelGridSizeWasChanged::withGridSize(
            $userId,
            $this->modflowId,
            $this->gridSize
        ));
    }

    public function changeBoundingBox(UserId $userId, BoundingBox $boundingBox)
    {
        $this->boundingBox = $boundingBox;
        $this->recordThat(ModflowModelBoundingBoxWasChanged::withBoundingBox(
            $userId,
            $this->modflowId,
            $this->boundingBox
        ));
    }

    public function addBoundary(ModflowBoundary $boundary): void
    {
        $this->boundaries[$boundary->boundaryId()->toString()] = $boundary;
    }

    public function addBoundaryToBaseModel(UserId $userId, ModflowBoundary $boundary){
        if (! $this->contains($boundary->boundaryId(), $this->boundaries)) {
            $this->addBoundary($boundary);
            $this->recordThat(BoundaryWasAdded::toBaseModel(
                $userId,
                $this->modflowId,
                $boundary
            ));
        }
    }

    public function addBoundaryToScenario(UserId $userId, ModflowId $scenarioId, ModflowBoundary $boundary)
    {
        if ($this->contains($scenarioId, $this->scenarios)) {
            /** @var ModflowModelAggregate $scenario */
            $scenario = $this->scenarios[$scenarioId->toString()];
            $scenario->addBoundary($boundary);
            $this->recordThat(BoundaryWasAddedToScenario::toScenario(
                $userId,
                $this->modflowId,
                $scenarioId,
                $boundary
            ));
        }
    }

    public function updateBoundary(ModflowBoundary $boundary): void
    {
        $this->boundaries[$boundary->boundaryId()->toString()] = $boundary;
    }

    public function updateBoundaryOfBaseModel(UserId $userId, ModflowBoundary $boundary): void
    {
        if ($this->contains($boundary->boundaryId(), $this->boundaries)) {
            $this->updateBoundary($boundary);
            $this->recordThat(ModflowModelBoundaryWasUpdated::ofBaseModel(
                $userId,
                $this->modflowId,
                $boundary
            ));
        }
    }

    public function updateBoundaryOfScenario(UserId $userId, ModflowId $scenarioId, ModflowBoundary $boundary): void
    {
        if ($this->contains($scenarioId, $this->scenarios)){
            /** @var ModflowModelAggregate $scenario */
            $scenario = $this->scenarios[$scenarioId->toString()];
            if ($scenario->contains($boundary->boundaryId(), $scenario->boundaries())){
                $scenario->updateBoundary($boundary);
            }

            $this->recordThat(ScenarioBoundaryWasUpdated::ofScenario(
                $userId,
                $this->modflowId,
                $scenarioId,
                $boundary
            ));
        }
    }

    public function changeSoilmodelId(SoilModelId $soilModelId): void
    {
        $this->soilmodelId = $soilModelId;
        $this->recordThat(ModflowModelSoilModelIdWasChanged::withSoilmodelId(
            $this->modflowId,
            $this->soilmodelId
        ));
    }

    public function removeBoundary(UserId $userId, BoundaryId $boundaryId)
    {
        if ($this->contains($boundaryId, $this->boundaries)) {
            unset($this->boundaries[$boundaryId->toString()]);
            $this->recordThat(BoundaryWasRemoved::withBoundaryId(
                $userId,
                $this->modflowId,
                $boundaryId
            ));
        }
    }

    public function removeBoundaryFromScenario(UserId $userId, ModflowId $scenarioId, BoundaryId $boundaryId)
    {
        if ($this->contains($scenarioId, $this->scenarios)) {
            $scenario = $this->scenarios[$scenarioId->toString()];
            if ($scenario->contains($boundaryId, $scenario->boundaries())) {
                $this->recordThat(BoundaryWasRemovedFromScenario::fromScenario(
                    $userId,
                    $this->modflowId,
                    $scenarioId,
                    $boundaryId
                ));
            }
        }
    }

    private function contains(IdInterface $needle, array $haystack): bool
    {
        return array_key_exists($needle->toString(), $haystack);
    }

    public function modflowModelId(): ModflowId
    {
        return $this->modflowId;
    }

    public function isScenario(): bool
    {
        return (! is_null($this->baseModelId));
    }

    public function ownerId(): UserId
    {
        return $this->owner;
    }

    public function name(): ModflowModelName
    {
        if ($this->name === null){
            $this->name = ModflowModelName::fromString('');
        }
        return $this->name;
    }

    public function description(): ModflowModelDescription
    {
        if ($this->description === null){
            $this->description = ModflowModelDescription::fromString('');
        }
        return $this->description;
    }

    public function gridSize(): GridSize
    {
        return $this->gridSize;
    }

    public function boundingBox(): BoundingBox
    {
        return $this->boundingBox;
    }

    public function area(): AbstractBoundary
    {
        return $this->area;
    }

    public function soilmodelId(): SoilModelId
    {
        return $this->soilmodelId;
    }

    public function boundaries(): array
    {
        return $this->boundaries;
    }

    public function scenarios(): array
    {
        return $this->scenarios;
    }

    protected function whenModflowModelWasCreated(ModflowModelWasCreated $event)
    {
        $this->modflowId = $event->modflowModelId();
        $this->owner = $event->userId();
        $this->boundaries = [];
        $this->scenarios = [];
    }

    protected function whenModflowScenarioWasAdded(ModflowScenarioWasAdded $event)
    {
        $scenario = $this->createScenarioFromThis($event->userId(), $event->scenarioId());
        if (! $this->contains($scenario->modflowId, $this->scenarios)){
            $this->scenarios[$scenario->modflowId->toString()] = $scenario;
        }
    }

    protected function whenModflowScenarioWasRemoved(ModflowScenarioWasRemoved $event)
    {
        if ($this->contains($event->scenarioId(), $this->scenarios)) {
            unset($this->scenarios[$event->scenarioId()->toString()]);
        }
    }

    protected function whenModflowModelNameWasChanged(ModflowModelNameWasChanged $event)
    {
        if ($event->userId()->sameValueAs($this->ownerId())){
            $this->name = $event->name();
        }
    }

    protected function whenModflowScenarioNameWasChanged(ModflowScenarioNameWasChanged $event)
    {
        if ($this->contains($event->scenarioId(), $this->scenarios)){
            /** @var ModflowModelAggregate $scenario */
            $scenario = $this->scenarios[$event->scenarioId()->toString()];
            $scenario->changeName($event->name());
        }
    }

    protected function whenModflowModelDescriptionWasChanged(ModflowModelDescriptionWasChanged $event)
    {
        if ($event->userId()->sameValueAs($this->ownerId())){
            $this->description = $event->description();
        }
    }

    protected function whenModflowScenarioDescriptionWasChanged(ModflowScenarioDescriptionWasChanged $event)
    {
        if ($this->contains($event->scenarioId(), $this->scenarios)){
            /** @var ModflowModelAggregate $scenario */
            $scenario = $this->scenarios[$event->scenarioId()->toString()];
            $scenario->changeDescription($event->description());
        }
    }

    protected function whenModflowModelGridSizeWasChanged(ModflowModelGridSizeWasChanged $event)
    {
        if ($event->userId()->sameValueAs($this->ownerId())) {
            $this->gridSize = $event->gridSize();
        }
    }

    protected function whenModflowModelBoundingBoxWasChanged(ModflowModelBoundingBoxWasChanged $event)
    {
        if ($event->userId()->sameValueAs($this->ownerId())){
            $this->boundingBox = $event->boundingBox();
        }
    }

    protected function whenModflowModelSoilModelIdWasChanged(ModflowModelSoilModelIdWasChanged $event)
    {
        $this->soilmodelId = $event->soilModelId();
    }

    protected function whenBoundaryWasAdded(BoundaryWasAdded $event)
    {
        $boundary = $event->boundary();
        if ($boundary instanceof AreaBoundary){
            $this->area = $boundary;
            return;
        }

        if (! $this->contains($boundary->boundaryId(), $this->boundaries)){
            $this->boundaries[$boundary->boundaryId()->toString()] = $boundary;
        }
    }

    protected function whenModflowModelBoundaryWasUpdated(ModflowModelBoundaryWasUpdated $event)
    {
        $boundary = $event->boundary();
        if ($boundary instanceof AreaBoundary){
            $this->area = $boundary;
            return;
        }

        $this->boundaries[$boundary->boundaryId()->toString()] = $event->boundary();
    }

    protected function whenBoundaryWasAddedToScenario(BoundaryWasAddedToScenario $event)
    {
        $boundary = $event->boundary();
        if ($boundary instanceof AreaBoundary){
            return;
        }

        if ($this->contains($event->scenarioId(), $this->scenarios)){
            $scenario = $this->scenarios[$event->scenarioId()->toString()];
            if (! $this->contains($boundary->boundaryId(), $scenario->boundaries)){
                $scenario->boundaries[$boundary->boundaryId()->toString()] = $boundary;
            }
        }
    }

    protected function whenScenarioBoundaryWasUpdated(ScenarioBoundaryWasUpdated $event)
    {
        $boundary = $event->boundary();
        if ($boundary instanceof AreaBoundary){
            return;
        }

        if ($this->contains($event->scenarioId(), $this->scenarios)){
            $scenario = $this->scenarios[$event->scenarioId()->toString()];
            $scenario->boundaries[$boundary->boundaryId()->toString()] = $event->boundary();
        }
    }

    protected function whenBoundaryWasRemoved(BoundaryWasRemoved $event)
    {
        if ($this->contains($event->boundaryId(), $this->boundaries)){
            unset($this->boundaries[$event->boundaryId()->toString()]);
        }
    }

    protected function whenBoundaryWasRemovedFromScenario(BoundaryWasRemovedFromScenario $event)
    {
        if ($this->contains($event->scenarioId(), $this->scenarios)){
            $scenario = $this->scenarios[$event->scenarioId()->toString()];

            if ($this->contains($event->boundaryId(), $scenario->boundaries)){
                unset($scenario->boundaries[$event->boundaryId()->toString()]);
            }
        }
    }

    private function createScenarioFromThis(UserId $userId, ModflowId $scenarioId): ModflowModelAggregate
    {
        $self = new self();
        $self->baseModelId = $this->modflowId;
        $self->modflowId = $scenarioId;
        $self->owner = $userId;
        $self->name = ModflowModelName::fromString('Scenario of '.$this->name()->toString());
        $self->description = $this->description();
        $self->gridSize = unserialize(serialize($this->gridSize));
        $self->boundingBox = unserialize(serialize($this->boundingBox));
        $self->area = unserialize(serialize($this->area));
        $self->soilmodelId = unserialize(serialize($this->soilmodelId));
        $self->boundaries = unserialize(serialize($this->boundaries));
        return $self;
    }

    protected function aggregateId(): string
    {
        return $this->modflowId->toString();
    }
}