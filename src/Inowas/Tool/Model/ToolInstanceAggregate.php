<?php

declare(strict_types=1);

namespace Inowas\Tool\Model;

use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\Name;
use Inowas\Tool\Model\Event\ToolInstanceDataWasUpdated;
use Inowas\Tool\Model\Event\ToolInstanceDescriptionWasUpdated;
use Inowas\Tool\Model\Event\ToolInstanceNameWasUpdated;
use Inowas\Tool\Model\Event\ToolInstanceWasCreated;
use Inowas\Tool\Model\Event\ToolInstanceWasDeleted;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;

class ToolInstanceAggregate extends AggregateRoot
{
    /** @var  ToolId */
    protected $id;

    /** @var  UserId */
    protected $userId;

    /** @var  ToolType */
    protected $type;

    /**
     * @return ToolId
     */
    public function id(): ToolId
    {
        return $this->id;
    }

    /**
     * @return UserId
     */
    public function userId(): UserId
    {
        return $this->userId;
    }

    /**
     * @return ToolType
     */
    public function type(): ToolType
    {
        return $this->type;
    }

    /**
     * @param ToolId $id
     * @param UserId $userId
     * @param ToolType $type
     * @return ToolInstanceAggregate
     */
    public static function create(
        ToolId $id,
        UserId $userId,
        ToolType $type
    ): ToolInstanceAggregate
    {
        $self = new self();
        $self->id = $id;
        $self->userId = $userId;
        $self->type = $type;

        $self->recordThat(ToolInstanceWasCreated::withParameters(
            $id,
            $userId,
            $type
        ));

        return $self;
    }

    public function updateName(UserId $userId, Name $name): void
    {
        $this->recordThat(ToolInstanceNameWasUpdated::withParameters(
            $this->id,
            $userId,
            $name
        ));
    }

    public function updateDescription(UserId $userId, Description $description): void
    {
        $this->recordThat(ToolInstanceDescriptionWasUpdated::withParameters(
            $this->id,
            $userId,
            $description
        ));
    }

    public function updateData(UserId $userId, ToolData $data): void
    {
        $this->recordThat(ToolInstanceDataWasUpdated::withParameters(
            $this->id,
            $userId,
            $data
        ));
    }

    public function delete(UserId $userId): void
    {
        $this->recordThat(ToolInstanceWasDeleted::withParameters(
            $this->id,
            $userId
        ));
    }


    protected function whenToolInstanceWasCreated(ToolInstanceWasCreated $event): void
    {
        $this->id = $event->id();
        $this->userId = $event->userId();
        $this->type = $event->type();
    }

    protected function whenToolInstanceNameWasUpdated(ToolInstanceNameWasUpdated $event): void
    {}

    protected function whenToolInstanceDescriptionWasUpdated(ToolInstanceDescriptionWasUpdated $event): void
    {}

    protected function whenToolInstanceDataWasUpdated(ToolInstanceDataWasUpdated $event): void
    {}

    protected function whenToolInstanceWasDeleted(ToolInstanceWasDeleted $event): void
    {}

    protected function aggregateId(): string
    {
        return $this->id->toString();
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
