<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model;

use Inowas\Common\Id\UserId;
use Inowas\Common\Soilmodel\BoreLogId;
use Inowas\Common\Soilmodel\BoreLogLocation;
use Inowas\Common\Soilmodel\BoreLogName;
use Inowas\Common\Soilmodel\Horizon;
use Inowas\Common\Soilmodel\HorizonId;
use Inowas\Soilmodel\Model\Event\BoreLogHorizonWasAdded;
use Inowas\Soilmodel\Model\Event\BoreLogHorizonWasRemoved;
use Inowas\Soilmodel\Model\Event\BoreLogLocationWasChanged;
use Inowas\Soilmodel\Model\Event\BoreLogNameWasChanged;
use Inowas\Soilmodel\Model\Event\BoreLogWasCreated;
use Inowas\Soilmodel\Model\Event\BoreLogWasDeleted;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;

class BoreLogAggregate extends AggregateRoot
{

    /** @var BoreLogId $boreLogId */
    protected $boreLogId;

    /** @var  UserId */
    protected $owner;

    /** @var  bool */
    protected $public;

    /** @var  BoreLogName */
    protected $name;

    /** @var  BoreLogLocation */
    protected $location;

    /** @var  array */
    protected $horizons;

    public static function create(UserId $userId, BoreLogId $boreLogId, BoreLogName $name, BoreLogLocation $location): BoreLogAggregate
    {
        $self = new self();
        $self->boreLogId = $boreLogId;
        $self->owner = $userId;
        $self->public = true;
        $self->name = BoreLogName::fromString("");
        $self->horizons = [];

        $self->recordThat(BoreLogWasCreated::byUserWithId($userId, $boreLogId, $name, $location));
        return $self;
    }

    public function delete(UserId $userId): void
    {
        $this->recordThat(BoreLogWasDeleted::byUserWithId($userId, $this->boreLogId));
    }

    public function changeName(UserId $userId, BoreLogName $name): void
    {
        $this->name = $name;
        $this->recordThat(BoreLogNameWasChanged::byUserWithName($userId, $this->boreLogId, $name));
    }

    public function changeLocation(UserId $userId, BoreLogLocation $location): void
    {
        $this->location = $location;
        $this->recordThat(BoreLogLocationWasChanged::byUserWithLocation($userId, $this->boreLogId, $location));
    }

    public function addHorizon(UserId $userId, Horizon $horizon): void
    {
        $this->horizons[$horizon->id()->toString()] = $horizon;
        $this->recordThat(BoreLogHorizonWasAdded::byUserWithHorizon($userId, $this->boreLogId, $horizon));
    }

    public function removeHorizon(UserId $userId, HorizonId $horizonId): void
    {
        if (array_key_exists($horizonId->toString(), $this->horizons)){
            unset($this->horizons[$horizonId->toString()]);
            $this->recordThat(BoreLogHorizonWasRemoved::byUserWithHorizonId($userId, $this->boreLogId, $horizonId));
        }
    }

    public function boreLogId(): BoreLogId
    {
        return $this->boreLogId;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function name(): BoreLogName
    {
        return $this->name;
    }

    public function location(): BoreLogLocation
    {
        return $this->location;
    }

    public function horizons(): array
    {
        return $this->horizons;
    }

    protected function whenBoreLogWasCreated(BoreLogWasCreated $event): void
    {
        $this->boreLogId = $event->boreLogId();
        $this->owner = $event->userId();
        $this->public = true;
        $this->name = $event->name();
        $this->location = $event->location();
        $this->horizons = [];
    }

    protected function whenBoreLogWasDeleted(BoreLogWasDeleted $event): void
    {}

    protected function whenBoreLogNameWasChanged(BoreLogNameWasChanged $event): void
    {
        $this->name = $event->name();
    }

    protected function whenBoreLogLocationWasChanged(BoreLogLocationWasChanged $event): void
    {
        $this->location = $event->location();
    }

    protected function whenBoreLogHorizonWasAdded(BoreLogHorizonWasAdded $event): void
    {
        $this->horizons[$event->horizon()->id()->toString()] = $event->horizon();
    }

    protected function whenBoreLogHorizonWasRemoved(BoreLogHorizonWasRemoved $event): void
    {
        if (array_key_exists($event->horizonId()->toString(), $this->horizons)){
            unset($this->horizons[$event->horizonId()->toString()]);
        }
    }

    /**
     * @return string
     */
    protected function aggregateId(): string
    {
        return $this->boreLogId->toString();
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
