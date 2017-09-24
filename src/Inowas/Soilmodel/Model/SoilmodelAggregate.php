<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model;

use Inowas\Common\Modflow\Botm;
use Inowas\Common\Id\UserId;
use Inowas\Common\Soilmodel\AbstractSoilproperty;
use Inowas\Common\Soilmodel\BoreLogId;
use Inowas\Common\Soilmodel\GeologicalLayer;
use Inowas\Common\Soilmodel\GeologicalLayerId;
use Inowas\Common\Soilmodel\GeologicalLayerNumber;
use Inowas\Common\Soilmodel\GeologicalLayerValues;
use Inowas\Common\Soilmodel\SoilmodelDescription;
use Inowas\Common\Soilmodel\SoilmodelId;
use Inowas\Common\Soilmodel\SoilmodelName;
use Inowas\Common\Soilmodel\TopElevation;
use Inowas\Soilmodel\Model\Event\LayerPropertyWasUpdated;
use Inowas\Soilmodel\Model\Event\LayerValuesWereUpdated;
use Inowas\Soilmodel\Model\Event\SoilmodelBoreLogWasAdded;
use Inowas\Soilmodel\Model\Event\SoilmodelBoreLogWasRemoved;
use Inowas\Soilmodel\Model\Event\SoilmodelDescriptionWasChanged;
use Inowas\Soilmodel\Model\Event\SoilmodelGeologicalLayerWasAdded;
use Inowas\Soilmodel\Model\Event\SoilmodelGeologicalLayerWasRemoved;
use Inowas\Soilmodel\Model\Event\SoilmodelNameWasChanged;
use Inowas\Soilmodel\Model\Event\SoilmodelWasCloned;
use Inowas\Soilmodel\Model\Event\SoilmodelWasCreated;
use Inowas\Soilmodel\Model\Event\SoilmodelWasDeleted;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;

class SoilmodelAggregate extends AggregateRoot
{

    /** @var  SoilmodelId */
    protected $soilmodelId;

    /** @var  UserId */
    protected $owner;

    /** @var  bool */
    protected $public;

    /** @var  SoilmodelName */
    protected $name;

    /** @var  SoilmodelDescription */
    protected $description;

    /** @var  array */
    protected $boreLogs;

    /** @var  array */
    protected $layers;

    public static function create(UserId $userId, SoilmodelId $soilmodelId): SoilmodelAggregate
    {
        $self = new self();
        $self->soilmodelId = $soilmodelId;
        $self->owner = $userId;
        $self->public = true;
        $self->name = SoilmodelName::fromString('');
        $self->description = SoilmodelDescription::fromString('');
        $self->boreLogs = [];
        $self->layers = [];

        $self->recordThat(SoilmodelWasCreated::byUserWithId($userId, $soilmodelId));
        return $self;
    }

    public static function clone(UserId $userId, SoilmodelId $newId, SoilmodelAggregate $soilmodel): SoilmodelAggregate
    {
        $self = new self();
        $self->soilmodelId = $newId;
        $self->owner = $userId;
        $self->public = true;
        $self->name = $soilmodel->name();
        $self->description = $soilmodel->description();
        $self->boreLogs = [];
        $self->layers = $soilmodel->layers();

        $self->recordThat(SoilmodelWasCloned::byUserWithIds(
            $newId,
            $soilmodel->id(),
            $userId,
            $self->public,
            $self->name(),
            $self->description(),
            $self->boreLogs(),
            $self->layers()
        ));

        return $self;
    }

    public function delete(UserId $userId): void
    {
        $this->recordThat(SoilmodelWasDeleted::byUserWithId($userId, $this->soilmodelId));
    }

    public function changeName(UserId $userId, SoilmodelName $name): void
    {
        $this->name = $name;
        $this->recordThat(SoilmodelNameWasChanged::byUserWithName($userId, $this->soilmodelId, $name));
    }

    public function changeDescription(UserId $userId, SoilmodelDescription $description): void
    {
        $this->description = $description;
        $this->recordThat(SoilmodelDescriptionWasChanged::byUserWithName($userId, $this->soilmodelId, $description));
    }

    public function addBoreLog(UserId $userId, BoreLogId $logId): void
    {
        if (! array_key_exists($logId->toString(), $this->boreLogs)){
            $this->boreLogs[$logId->toString()] = true;
            $this->recordThat(SoilmodelBoreLogWasAdded::byUserWithId($userId, $this->soilmodelId, $logId));
        }
    }

    public function removeBoreLog(UserId $userId, BoreLogId $logId): void
    {
        if (array_key_exists($logId->toString(), $this->boreLogs)){
           unset($this->boreLogs[$logId->toString()]);
            $this->recordThat(SoilmodelBoreLogWasRemoved::byUserWithId($userId, $this->soilmodelId, $logId));
        }
    }

    public function addGeologicalLayer(UserId $userId, GeologicalLayer $layer): void
    {
        $this->layers[$layer->id()->toString()] = $layer;
        $this->recordThat(SoilmodelGeologicalLayerWasAdded::byUserWithId($userId, $this->soilmodelId, $layer));
    }

    public function removeGeologicalLayer(UserId $userId, GeologicalLayer $layer): void
    {
        if (array_key_exists($layer->id()->toString(), $this->layers)){
            unset($this->layers[$layer->id()->toString()]);
            $this->recordThat(SoilmodelGeologicalLayerWasRemoved::byUserWithId($userId, $this->soilmodelId, $layer->id()));
        }
    }

    public function getGeologicalLayer(GeologicalLayerId $id): ?GeologicalLayer
    {
        if (array_key_exists($id->toString(), $this->layers)){
            return $this->layers[$id->toString()];
        }

        return null;
    }

    public function updateGeologicalLayerProperty(UserId $userId, GeologicalLayerId $layerId, AbstractSoilproperty $property): void
    {

        if (! array_key_exists($layerId->toString(), $this->layers)){
            return;
        }

        $this->recordThat(LayerPropertyWasUpdated::forSoilmodelAndLayer($userId, $this->soilmodelId, $layerId, $property));
    }

    public function updateGeologicalLayerValues(GeologicalLayerId $layerId, GeologicalLayerNumber $layerNumber, GeologicalLayerValues $values): void
    {

        if (! array_key_exists($layerId->toString(), $this->layers)){
            return;
        }

        /** @var GeologicalLayer $layer */
        $layer = $this->layers[$layerId->toString()];
        $this->layers[$layerId->toString()] = $layer->updateValues($values);
        $this->recordThat(LayerValuesWereUpdated::forSoilmodelAndLayer($this->soilmodelId, $layerId, $layerNumber, $values));
    }

    protected function whenSoilmodelWasCreated(SoilmodelWasCreated $event): void
    {
        $this->soilmodelId = $event->soilmodelId();
        $this->owner = $event->userId();
        $this->name = SoilmodelName::fromString('');
        $this->description = SoilmodelDescription::fromString('');
        $this->public = true;
        $this->boreLogs = [];
        $this->layers = [];
    }

    protected function whenSoilmodelWasCloned(SoilmodelWasCloned $event): void
    {
        $this->soilmodelId = $event->soilmodelId();
        $this->owner = $event->userId();
        $this->public = $event->isPublic();
        $this->name = $event->name();
        $this->description = $event->description();
        $this->boreLogs = $event->borelogs();
        $this->layers = $event->layers();
    }

    protected function whenSoilmodelWasDeleted(SoilmodelWasDeleted $event): void
    {}

    protected function whenSoilmodelNameWasChanged(SoilmodelNameWasChanged $event): void
    {
        $this->name = $event->name();
    }

    protected function whenSoilmodelDescriptionWasChanged(SoilmodelDescriptionWasChanged $event): void
    {
        $this->description = $event->description();
    }

    protected function whenSoilmodelBoreLogWasAdded(SoilmodelBoreLogWasAdded $event): void
    {
        $this->boreLogs[$event->boreLogId()->toString()] = true;
    }

    protected function whenSoilmodelBoreLogWasRemoved(SoilmodelBoreLogWasRemoved $event): void
    {
        if (array_key_exists($event->boreLogId()->toString(), $this->boreLogs)){
            unset($this->boreLogs[$event->boreLogId()->toString()]);
        }
    }

    protected function whenSoilmodelGeologicalLayerWasAdded(SoilmodelGeologicalLayerWasAdded $event): void
    {
        $this->layers[$event->layer()->id()->toString()] = $event->layer();
    }

    protected function whenSoilmodelGeologicalLayerWasRemoved(SoilmodelGeologicalLayerWasRemoved $event): void
    {
        if (array_key_exists($event->layerId()->toString(), $this->layers)) {
            unset($this->layers[$event->layerId()->toString()]);
        }
    }

    protected function whenLayerPropertyWasUpdated(LayerPropertyWasUpdated $event): void
    {
        /** @var GeologicalLayer $layer */
        $layer = $this->layers[$event->layerId()->toString()];
        $this->layers[$event->layerId()->toString()] = $layer->updateProperty($event->property());
    }

    protected function whenLayerValuesWereUpdated(LayerValuesWereUpdated $event): void
    {
        $layer = $this->layers[$event->layerId()->toString()];
        $this->layers[$event->layerId()->toString()] = $layer->updateValues($event->values());
    }

    public function id(): SoilmodelId
    {
        return $this->soilmodelId;
    }

    public function ownerId(): UserId
    {
        return $this->owner;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function name(): SoilmodelName
    {
        return $this->name;
    }

    public function description(): SoilmodelDescription
    {
        return $this->description;
    }

    public function boreLogs(): array
    {
        return $this->boreLogs;
    }

    public function layers(): array
    {
        return $this->layers;
    }

    public function topElevation(): TopElevation
    {
        $topLayer = $this->layer(GeologicalLayerNumber::fromInteger(0));
        return $topLayer->values()->hTop();
    }

    public function bottomElevation(): Botm
    {
        $layers = $this->layers();
        usort($layers, function ($a, $b){return $a->layerNumber()->toInteger() > $b->layerNumber()->toInteger();});

        $hBot = [];

        /** @var GeologicalLayer $layer */
        foreach ($layers as $layer){
            $hBot[] = $layer->values()->hBottom();
        }

        return Botm::from3DArray($hBot);
    }

    public function userHasWriteAccess(UserId $userId): bool
    {
        if ($userId->sameValueAs($this->owner)){
            return true;
        }

        return false;
    }

    private function layer(GeologicalLayerNumber $layerNumber): ?GeologicalLayer
    {
        /** @var GeologicalLayer $layer */
        foreach ($this->layers() as $layer) {
            if ($layer->layerNumber()->sameAs($layerNumber)) {
                return $layer;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    protected function aggregateId(): string
    {
        return $this->soilmodelId->toString();
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
