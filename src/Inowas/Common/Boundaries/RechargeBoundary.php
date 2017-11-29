<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\AffectedCells;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ObservationPointId;
use Inowas\Common\Modflow\Name;

class RechargeBoundary extends ModflowBoundary
{
    public const CARDINALITY = '1';
    public const TYPE = 'rch';

    /** @var  ObservationPoint */
    protected $observationPoint;

    public static function fromArray(array $arr): ModflowBoundary
    {
        $affectedCells = AffectedCells::create();
        if (array_key_exists('active_cells', $arr)) {
            $affectedCells = AffectedCells::fromArray($arr['active_cells']);
        }

        $self = new self(
            BoundaryId::fromString($arr['id']),
            Name::fromString($arr['name']),
            Geometry::fromArray($arr['geometry']),
            $affectedCells,
            AffectedLayers::fromArray($arr['affected_layers']),
            Metadata::fromArray((array)$arr['metadata'])
        );

        /** @var array $dateTimeValues */
        $dateTimeValues = $arr['date_time_values'];
        foreach ($dateTimeValues as $date_time_value) {
            $self->addRecharge(
                RechargeDateTimeValue::fromParams(
                    DateTime::fromAtom($date_time_value['date_time']),
                    $date_time_value['values'][0]
                )
            );
        }

        return $self;
    }

    public function addRecharge(RechargeDateTimeValue $rechargeRate): ModflowBoundary
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

        $this->addDateTimeValue($rechargeRate, $observationPointId);
        return $this->self();
    }

    public function findValueByDateTime(DateTime $dateTime): RechargeDateTimeValue
    {
        /** @var ObservationPoint $op */
        $observationPointId = ObservationPointId::fromString('OP');
        $op = $this->getObservationPoint($observationPointId);
        $value = $op->findValueByDateTime($dateTime);

        if ($value instanceof RechargeDateTimeValue){
            return $value;
        }

        return RechargeDateTimeValue::fromParams($dateTime, 0);
    }

    public function toArray(): array
    {
        return array(
            'id' => $this->boundaryId()->toString(),
            'type' => $this->type()->toString(),
            'name' => $this->name()->toString(),
            'geometry' => $this->geometry()->toArray(),
            'active_cells' => $this->affectedCells()->toArray(),
            'affected_layers' => $this->affectedLayers()->toArray(),
            'metadata' => $this->metadata()->toArray(),
            'date_time_values' => $this->getObservationPoint(ObservationPointId::fromString('OP'))->dateTimeValues()->toArray()
        );
    }
}
