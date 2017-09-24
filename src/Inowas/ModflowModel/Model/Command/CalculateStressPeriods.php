<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;

class CalculateStressPeriods extends AbstractJsonSchemaCommand
{

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ModflowId $modelId
     * @param DateTime $start
     * @param DateTime $end
     * @param bool $initialSteady
     * @return CalculateStressPeriods
     */
    public static function forModflowModel(UserId $userId, ModflowId $modelId, DateTime $start, DateTime $end, bool $initialSteady = false): CalculateStressPeriods
    {
        $self = new static(
            [
                'id' => $modelId->toString(),
                'start' => $start->toAtom(),
                'end' => $end->toAtom(),
                'initial_steady' => $initialSteady
            ]
        );
        
        /** @var CalculateStressPeriods $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public function schema(): string
    {
        return 'file://spec/schema/modflow/command/calculateStressperiodsPayload.json';
    }

    public function modflowId(): ModflowId
    {
        return ModflowId::fromString($this->payload['id']);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->metadata['user_id']);
    }

    public function start(): DateTime
    {
        return DateTime::fromAtom($this->payload['start']);
    }

    public function end(): DateTime
    {
        return DateTime::fromAtom($this->payload['end']);
    }

    public function initialStressPeriodSteady(): bool
    {
        if (! array_key_exists('initial_steady', $this->payload)) {
            return false;
        }

        return $this->payload['initial_steady'];
    }
}
