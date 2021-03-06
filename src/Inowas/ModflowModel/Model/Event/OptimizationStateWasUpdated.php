<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Event;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\OptimizationState;
use Inowas\ModflowModel\Model\AMQP\ModflowOptimizationResponse;
use Prooph\EventSourcing\AggregateChanged;

class OptimizationStateWasUpdated extends AggregateChanged
{
    /** @var ModflowId */
    private $modflowId;

    /** @var ModflowId */
    private $optimizationId;

    /** @var  OptimizationState */
    private $state;

    /** @var  ModflowOptimizationResponse */
    private $response;

    /** @var  UserId */
    private $userId;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ModflowId $modflowId
     * @param ModflowId $optimizationId
     * @param OptimizationState $state
     * @return self
     */
    public static function withModelIdAndState(ModflowId $modflowId, ModflowId $optimizationId, OptimizationState $state): self
    {
        /** @var self $event */
        $event = self::occur(
            $modflowId->toString(), [
                'optimization_id' => $optimizationId->toString(),
                'state' => $state->toInt()
            ]
        );

        $event->modflowId = $modflowId;
        $event->optimizationId = $optimizationId;
        $event->state = $state;

        return $event;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ModflowId $modflowId
     * @param ModflowId $optimizationId
     * @param OptimizationState $state
     * @param ModflowOptimizationResponse $response
     * @return self
     */
    public static function withModelIdStateAndResponse(ModflowId $modflowId, ModflowId $optimizationId, OptimizationState $state, ModflowOptimizationResponse $response): self
    {
        /** @var self $event */
        $event = self::occur(
            $modflowId->toString(), [
                'optimization_id' => $optimizationId->toString(),
                'state' => $state->toInt(),
                'response' => $response->toArray()
            ]
        );

        $event->modflowId = $modflowId;
        $event->optimizationId = $optimizationId;
        $event->response = $response;
        $event->state = $state;

        return $event;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ModflowId $modflowId
     * @param ModflowId $optimizationId
     * @param OptimizationState $state
     * @return self
     */
    public static function withUserIdModelIdAndState(UserId $userId, ModflowId $modflowId, ModflowId $optimizationId, OptimizationState $state): self
    {
        /** @var self $event */
        $event = self::occur(
            $modflowId->toString(), [
                'user_id' => $userId->toString(),
                'optimization_id' => $optimizationId->toString(),
                'state' => $state->toInt()
            ]
        );

        $event->modflowId = $modflowId;
        $event->state = $state;
        $event->optimizationId = $optimizationId;

        return $event;
    }

    public function modelId(): ModflowId
    {
        if ($this->modflowId === null) {
            $this->modflowId = ModflowId::fromString($this->aggregateId());
        }

        return $this->modflowId;
    }

    public function optimizationId(): ModflowId
    {
        if ($this->optimizationId === null) {
            $this->optimizationId = ModflowId::fromString($this->payload['optimization_id']);
        }

        return $this->optimizationId;
    }

    public function state(): OptimizationState
    {
        if ($this->state === null) {
            $this->state = OptimizationState::fromInt($this->payload['state']);
        }

        return $this->state;
    }

    public function userId(): ?UserId
    {
        if (!\array_key_exists('user_id', $this->payload)) {
            return null;
        }

        if ($this->userId === null) {
            $this->userId = UserId::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }

    public function response(): ?ModflowOptimizationResponse
    {
        if (!\array_key_exists('response', $this->payload)) {
            return null;
        }

        if ($this->response === null) {
            $this->response = ModflowOptimizationResponse::fromArray($this->payload['response']);
        }

        return $this->response;
    }
}
