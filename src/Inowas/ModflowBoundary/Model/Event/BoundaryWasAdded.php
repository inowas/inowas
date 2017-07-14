<?php

declare(strict_types=1);

namespace Inowas\ModflowBoundary\Model\Event;

use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\Name;
use Prooph\EventSourcing\AggregateChanged;

/** @noinspection LongInheritanceChainInspection */
class BoundaryWasAdded extends AggregateChanged
{

    /** @var BoundaryId */
    private $boundaryId;

    /** @var ModflowId */
    private $modelId;

    /** @var BoundaryType */
    private $boundaryType;

    /** @var Name */
    private $boundaryName;

    /** @var Geometry */
    private $geometry;

    /** @var AffectedLayers */
    private $affectedLayers;

    /** @var Metadata */
    private $boundaryMetadata;

    /** @var UserId */
    private $userId;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param BoundaryId $boundaryId
     * @param ModflowId $modelId
     * @param UserId $userId
     * @param BoundaryType $boundaryType
     * @param Name $boundaryName
     * @param Geometry $geometry
     * @param AffectedLayers $affectedLayers
     * @param Metadata $boundaryMetadata
     * @return BoundaryWasAdded
     */
    public static function toModelWithParameters(
        BoundaryId $boundaryId,
        ModflowId $modelId,
        UserId $userId,
        BoundaryType $boundaryType,
        Name $boundaryName,
        Geometry $geometry,
        AffectedLayers $affectedLayers,
        Metadata $boundaryMetadata
    ): BoundaryWasAdded
    {
        $event = self::occur(
            $boundaryId->toString(), [
                'model_id' => $modelId->toString(),
                'user_id' => $userId->toString(),
                'type' => $boundaryType->toString(),
                'name' => $boundaryName->toString(),
                'geometry' => $geometry->toArray(),
                'affected_layers' => $affectedLayers->toArray(),
                'boundary_metadata' => $boundaryMetadata->toArray()
            ]
        );

        $event->affectedLayers = $affectedLayers;
        $event->boundaryId = $boundaryId;
        $event->boundaryType = $boundaryType;
        $event->boundaryName = $boundaryName;
        $event->geometry = $geometry;
        $event->boundaryMetadata = $boundaryMetadata;
        $event->userId = $userId;
        $event->modelId = $modelId;

        return $event;
    }

    public function boundaryId(): BoundaryId
    {
        if (null === $this->boundaryId) {
            $this->boundaryId = BoundaryId::fromString($this->aggregateId());
        }

        return $this->boundaryId;
    }

    public function modelId(): ModflowId
    {
        if (null === $this->modelId) {
            $this->modelId = ModflowId::fromString($this->payload['model_id']);
        }

        return $this->modelId;
    }

    public function userId(): UserId
    {
        if (null === $this->userId) {
            $this->userId = BoundaryType::fromString($this->payload['user_id']);
        }

        return $this->userId;
    }


    public function type(): BoundaryType
    {
        if (null === $this->boundaryType) {
            $this->boundaryType = BoundaryType::fromString($this->payload['type']);
        }

        return $this->boundaryType;
    }

    public function name(): Name
    {
        if (null === $this->boundaryName) {
            $this->boundaryName = Name::fromString($this->payload['name']);
        }

        return $this->boundaryName;
    }

    public function geometry(): Geometry
    {
        if (null === $this->geometry) {
            $this->geometry = Geometry::fromArray($this->payload['geometry']);
        }

        return $this->geometry;
    }

    public function affectedLayers(): AffectedLayers
    {
        if (null === $this->affectedLayers) {
            $this->affectedLayers = AffectedLayers::fromArray($this->payload['affected_layers']);
        }

        return $this->affectedLayers;
    }

    public function metadata(): Metadata
    {
        if (null === $this->boundaryMetadata) {
            $this->boundaryMetadata = Metadata::fromArray($this->payload['boundary_metadata']);
        }

        return $this->boundaryMetadata;
    }
}