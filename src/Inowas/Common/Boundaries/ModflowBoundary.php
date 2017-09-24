<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ObservationPointId;
use Inowas\Common\Modflow\Name;

class ModflowBoundary
{
    CONST CARDINALITY = '';
    CONST TYPE = '';

    /** @var  BoundaryId */
    protected $id;

    /** @var  Name */
    protected $name;

    /** @var  Geometry */
    protected $geometry;

    /** @var  AffectedLayers */
    protected $affectedLayers;

    /** @var  Metadata */
    protected $metadata;

    /** @var ObservationPointCollection  */
    protected $observationPoints;

    protected function self(): ModflowBoundary
    {
        $self = new static($this->id, $this->name, $this->geometry, $this->affectedLayers, $this->metadata);
        $self->observationPoints = $this->observationPoints;
        $self->id = $this->id;
        return $self;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param Name $name
     * @param Geometry $geometry
     * @param AffectedLayers $affectedLayers
     * @param Metadata $metadata
     * @return ModflowBoundary
     */
    public static function createWithParams(
        Name $name,
        Geometry $geometry,
        AffectedLayers $affectedLayers,
        Metadata $metadata
    ): ModflowBoundary
    {
        return new static(BoundaryId::fromString($name->slugified()), $name, $geometry, $affectedLayers, $metadata);
    }

    public static function fromArray(array $arr): ModflowBoundary
    {
        $static = new static(
            BoundaryId::fromString($arr['id']),
            Name::fromString($arr['name']),
            Geometry::fromArray($arr['geometry']),
            AffectedLayers::fromArray($arr['affected_layers']),
            Metadata::fromArray($arr['metadata'])
        );

        $static->id = BoundaryId::fromString($arr['id']);
        $static->observationPoints = ObservationPointCollection::fromArray($arr['observation_points'], BoundaryType::fromString(static::TYPE));
        return $static;
    }

    protected function __construct(BoundaryId $id, Name $name, Geometry $geometry, AffectedLayers $affectedLayers, Metadata $metadata)
    {
        $this->id = $id;
        $this->name = $name;
        $this->geometry = $geometry;
        $this->affectedLayers = $affectedLayers;
        $this->metadata = $metadata;
        $this->observationPoints = ObservationPointCollection::create();
    }

    public function updateName(Name $boundaryName): ModflowBoundary
    {
        $this->name = $boundaryName;
        return $this->self();
    }

    public function updateGeometry(Geometry $geometry): ModflowBoundary
    {
        $this->geometry = $geometry;
        return $this->self();
    }

    public function updateAffectedLayers(AffectedLayers $affectedLayers): ModflowBoundary
    {
        $this->affectedLayers = $affectedLayers;
        return $this->self();
    }

    public function updateMetadata(Metadata $metadata): ModflowBoundary
    {
        $this->metadata = $metadata;
        return $this->self();
    }

    public function addObservationPoint(ObservationPoint $point): ModflowBoundary
    {
        $this->observationPoints()->add($point);
        return $this->self();
    }

    public function getObservationPoint(ObservationPointId $id): ObservationPoint
    {
        return $this->observationPoints->get($id);
    }

    public function updateObservationPoint(ObservationPoint $point): ModflowBoundary
    {
        $this->observationPoints()->add($point);
        return $this->self();
    }

    public function affectedLayers(): AffectedLayers
    {
        return $this->affectedLayers;
    }

    public function geometry(): Geometry
    {
        return $this->geometry;
    }

    public function boundaryId(): BoundaryId
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function metadata(): Metadata
    {
        if (null === $this->metadata){
            $this->metadata = Metadata::create();
        }

        return $this->metadata;
    }

    public function observationPoints(): ObservationPointCollection
    {
        return $this->observationPoints;
    }

    public function dateTimeValues(ObservationPointId $id): DateTimeValuesCollection
    {
        /** @var ObservationPoint $observationPoint */
        $observationPoint = $this->observationPoints->get($id);
        return $observationPoint->dateTimeValues();
    }

    public function getDateTimes(): array
    {
        return $this->observationPoints()->getDateTimes();
    }

    public function type(): BoundaryType
    {
        return BoundaryType::fromString($this::TYPE);
    }

    public function cardinality(): Cardinality
    {
        return Cardinality::fromString($this::CARDINALITY);
    }

    public function toArray(): array
    {
        return array(
            'id' => $this->id->toString(),
            'type' => $this->type()->toString(),
            'name' => $this->name()->toString(),
            'geometry' => $this->geometry()->toArray(),
            'affected_layers' => $this->affectedLayers()->toArray(),
            'metadata' => $this->metadata()->toArray(),
            'observation_points' => $this->observationPoints()->toArray()
        );
    }

    protected function hasObservationPoint(ObservationPointId $id): bool
    {
        return $this->observationPoints->has($id);
    }

    protected function addDateTimeValue(DateTimeValue $dateTimeValue, ObservationPointId $id)
    {
        $observationPoint = $this->observationPoints()->get($id);
        $observationPoint->addDateTimeValue($dateTimeValue);
        return $this;
    }
}
