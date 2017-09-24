<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Status\Visibility;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisDescriptionWasChanged;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisNameWasChanged;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisVisibilityWasChanged;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasCloned;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasCreated;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasDeleted;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioWasCreated;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioWasDeleted;
use Prooph\EventSourcing\AggregateChanged;
use Prooph\EventSourcing\AggregateRoot;

class ScenarioAnalysisAggregate extends AggregateRoot
{

    /** @var  ScenarioAnalysisId */
    protected $id;

    /** @var  ModflowId */
    protected $baseModelId;

    /** @var  UserId */
    protected $ownerId;

    /** @var  bool */
    protected $public;

    /** @var ScenarioAnalysisName */
    protected $name;

    /** @var ScenarioAnalysisDescription */
    protected $description;

    /** @var array */
    protected $scenarios;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ScenarioAnalysisId $id
     * @param UserId $userId
     * @param ModflowId $baseModelId
     * @param ScenarioAnalysisName $name
     * @param ScenarioAnalysisDescription $description
     * @return ScenarioAnalysisAggregate
     */
    public static function create(ScenarioAnalysisId $id, UserId $userId, ModflowId $baseModelId, ScenarioAnalysisName $name, ScenarioAnalysisDescription $description): ScenarioAnalysisAggregate
    {
        $self = new self();
        $self->id = $id;
        $self->baseModelId = $baseModelId;
        $self->ownerId = $userId;
        $self->name = $name;
        $self->description = $description;
        $self->scenarios = [];

        $self->recordThat(ScenarioAnalysisWasCreated::byUserWithId($id, $userId, $baseModelId, $name, $description));
        return $self;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ScenarioAnalysisId $fromId
     * @param ScenarioAnalysisId $id
     * @param UserId $userId
     * @param ModflowId $baseModelId
     * @param array $scenarios
     * @param ScenarioAnalysisAggregate $scenarioAnalysis
     * @return ScenarioAnalysisAggregate
     */
    public static function cloneWithIdUserIdAndAggregate(ScenarioAnalysisId $fromId, ScenarioAnalysisId $id, UserId $userId, ModflowId $baseModelId, array $scenarios, ScenarioAnalysisAggregate $scenarioAnalysis): ScenarioAnalysisAggregate
    {
        $self = new self();
        $self->id = $id;
        $self->ownerId = $userId;
        $self->baseModelId = $baseModelId;
        $self->name = $scenarioAnalysis->name();
        $self->description = $scenarioAnalysis->description();
        $self->scenarios = $scenarios;

        $self->recordThat(ScenarioAnalysisWasCloned::byUserWithId(
            $fromId,
            $id,
            $userId,
            $baseModelId,
            $self->name,
            $self->description,
            $scenarios
        ));

        return $self;
    }

    public function delete(UserId $userId): void
    {
        $this->recordThat(ScenarioAnalysisWasDeleted::byUser($this->id, $userId));
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ModflowId $scenarioId
     * @param ModflowId $baseModelId
     */
    public function createScenario(UserId $userId, ModflowId $scenarioId, ModflowId $baseModelId): void
    {
        if (in_array($scenarioId->toString(), $this->scenarios, true)){
            return;
        }

        $this->scenarios[] = $scenarioId->toString();
        $this->recordThat(ScenarioWasCreated::from($this->id, $userId, $scenarioId, $baseModelId));
    }

    public function deleteScenario(UserId $userId, ModflowId $scenarioId): void
    {
        if (! in_array($scenarioId->toString(), $this->scenarios, true)){
            return;
        }

        $this->scenarios = array_diff($this->scenarios, [$scenarioId->toString()]);
        $this->recordThat(ScenarioWasDeleted::from($this->id, $userId, $scenarioId));
    }

    public function changeName(UserId $userId, ScenarioAnalysisName $name): void
    {
        $this->name = $name;
        $this->recordThat(ScenarioAnalysisNameWasChanged::of($this->id, $userId, $name));
    }

    public function changeDescription(UserId $userId, ScenarioAnalysisDescription $description): void
    {
        $this->description = $description;
        $this->recordThat(ScenarioAnalysisDescriptionWasChanged::of($this->id, $userId, $description));
    }

    public function changeVisibility(UserId $userId, Visibility $visibility): void
    {
        $this->recordThat(ScenarioAnalysisVisibilityWasChanged::of($this->id, $userId, $visibility));
    }

    public function scenarioAnalysisId(): ScenarioAnalysisId
    {
        return $this->id;
    }

    public function baseModelId(): ModflowId
    {
        return $this->baseModelId;
    }

    public function ownerId(): UserId
    {
        return $this->ownerId;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function name(): ScenarioAnalysisName
    {
        return $this->name;
    }

    public function description(): ScenarioAnalysisDescription
    {
        return $this->description;
    }

    public function scenarios(): array
    {
        return $this->scenarios;
    }

    protected function whenScenarioAnalysisWasCreated(ScenarioAnalysisWasCreated $event): void
    {
        $this->id = $event->scenarioAnalysisId();
        $this->baseModelId = $event->baseModelId();
        $this->ownerId = $event->userId();
        $this->name = $event->name();
        $this->description = $event->description();
        $this->scenarios = [];
    }

    protected function whenScenarioAnalysisWasCloned(ScenarioAnalysisWasCloned $event): void
    {
        $this->id = $event->scenarioAnalysisId();
        $this->baseModelId = $event->baseModelId();
        $this->ownerId = $event->userId();
        $this->name = $event->name();
        $this->description = $event->description();
        $this->scenarios = $event->scenarios();
    }

    protected function whenScenarioAnalysisWasDeleted(ScenarioAnalysisWasDeleted $event): void
    {}

    protected function whenScenarioWasCreated(ScenarioWasCreated $event): void
    {
        $this->scenarios[] = $event->scenarioId()->toString();
    }

    protected function whenScenarioWasDeleted(ScenarioWasDeleted $event): void
    {
        $this->scenarios = array_diff($this->scenarios, [$event->scenarioId()->toString()]);
    }

    protected function whenScenarioAnalysisNameWasChanged(ScenarioAnalysisNameWasChanged $event): void
    {
        $this->name = $event->name();
    }

    protected function whenScenarioAnalysisDescriptionWasChanged(ScenarioAnalysisDescriptionWasChanged $event): void
    {
        $this->description = $event->description();
    }

    protected function whenScenarioAnalysisVisibilityWasChanged(ScenarioAnalysisVisibilityWasChanged $event): void
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
