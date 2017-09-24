<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Geometry\Point;
use Inowas\Common\Id\ObservationPointId;
use Inowas\Common\Modflow\Name;

class ObservationPoint implements \JsonSerializable
{
    /** @var  ObservationPointId */
    protected $id;

    /** @var  Point */
    protected $geometry;

    /** @var  Name */
    protected $name;

    /** @var  BoundaryType */
    protected $type;

    /** @var  DateTimeValuesCollection */
    protected $dateTimeValuesCollection;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ObservationPointId $id
     * @param BoundaryType $type
     * @param Name $name
     * @param Point $geometry
     * @return ObservationPoint
     */
    public static function fromIdTypeNameAndGeometry(ObservationPointId $id, BoundaryType $type, Name $name, Point $geometry): ObservationPoint
    {
        $self = new self($id, $type, $name, $geometry);
        $self->dateTimeValuesCollection = DateTimeValuesCollection::create();
        return $self;
    }

    public static function fromArray(array $arr, BoundaryType $type): ObservationPoint
    {
        $self = new self(
            ObservationPointId::fromString($arr['id']),
            $type,
            Name::fromString($arr['name']),
            Geometry::fromArray($arr['geometry'])->getPoint()
        );

        $self->dateTimeValuesCollection = DateTimeValuesCollection::fromTypeAndArray($type, $arr['date_time_values']);
        return $self;
    }

    private function __construct(ObservationPointId $id, BoundaryType $type, Name $name, Point $geometry)
    {
        $this->id = $id;
        $this->name = $name;
        $this->geometry = $geometry;
        $this->type = $type;
    }

    public function addDateTimeValue(DateTimeValue $dateTimeValue): ObservationPoint
    {
        $this->dateTimeValuesCollection->add($dateTimeValue);
        $self = new self($this->id, $this->type, $this->name, $this->geometry);
        $self->dateTimeValuesCollection = $this->dateTimeValuesCollection;
        return $self;
    }

    public function type(): BoundaryType
    {
        return $this->type;
    }

    public function geometry(): Point
    {
        return $this->geometry;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function getDateTimes(): array
    {
        return $this->dateTimeValues()->getDateTimes();
    }

    public function dateTimeValues(): DateTimeValuesCollection
    {
        return $this->dateTimeValuesCollection;
    }

    public function toArray(): array
    {
        return array(
            'id' => $this->id->toString(),
            'name' => $this->name()->toString(),
            'geometry' => Geometry::fromPoint($this->geometry)->toArray(),
            'type' => $this->type->toString(),
            'date_time_values' => $this->dateTimeValuesCollection->toArray()
        );
    }

    public function jsonSerialize(): array
    {
        return array(
            'id' => $this->id->toString(),
            'name' => $this->name()->toString(),
            'geometry' => $this->geometry->toArray(),
            'values' => $this->dateTimeValuesCollection
        );
    }

    public function findValueByDateTime(DateTime $dateTime): ?DateTimeValue
    {
        return $this->dateTimeValues()->findValueByDateTime($dateTime);
    }

    public function id(): ObservationPointId
    {
        return $this->id;
    }
}
