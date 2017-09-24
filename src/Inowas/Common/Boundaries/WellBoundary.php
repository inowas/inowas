<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ObservationPointId;
use Inowas\Common\Modflow\Name;

class WellBoundary extends ModflowBoundary
{
    const CARDINALITY = '1';
    const TYPE = 'wel';

    public static function fromArray(array $arr): ModflowBoundary
    {
        $self = new self(
            BoundaryId::fromString($arr['id']),
            Name::fromString($arr['name']),
            Geometry::fromArray($arr['geometry']),
            AffectedLayers::fromArray($arr['affected_layers']),
            Metadata::fromArray((array)$arr['metadata'])
        );

        /** @var array $dateTimeValues */
        $dateTimeValues = $arr['date_time_values'];
        foreach ($dateTimeValues as $date_time_value) {
            $self->addPumpingRate(
                WellDateTimeValue::fromParams(
                    DateTime::fromAtom($date_time_value['date_time']),
                    $date_time_value['values'][0]
                )
            );
        }

        return $self;
    }

    public function addPumpingRate(WellDateTimeValue $pumpingRate): WellBoundary
    {
        $observationPointId = ObservationPointId::fromString('OP');
        if (! $this->hasObservationPoint($observationPointId)) {
            $this->addObservationPoint(
                ObservationPoint::fromIdTypeNameAndGeometry(
                    $observationPointId,
                    $this->type(),
                    Name::fromString($this->name->toString()),
                    $this->geometry->getPointFromGeometry()
                )
            );
        }

        $this->addDateTimeValue($pumpingRate, $observationPointId);
        return $this->self();
    }

    public function findValueByDateTime(DateTime $dateTime): WellDateTimeValue
    {
        /** @var ObservationPoint $op */
        $op = $this->getObservationPoint(ObservationPointId::fromString('OP'));
        $value = $op->findValueByDateTime($dateTime);

        if ($value instanceof WellDateTimeValue){
            return $value;
        }

        return WellDateTimeValue::fromParams($dateTime, 0);
    }

    public function toArray(): array
    {
        return array(
            'id' => $this->boundaryId()->toString(),
            'type' => $this->type()->toString(),
            'name' => $this->name()->toString(),
            'geometry' => $this->geometry()->toArray(),
            'affected_layers' => $this->affectedLayers()->toArray(),
            'metadata' => $this->metadata()->toArray(),
            'date_time_values' => $this->getObservationPoint(ObservationPointId::fromString('OP'))->dateTimeValues()->toArray()
        );
    }
}
