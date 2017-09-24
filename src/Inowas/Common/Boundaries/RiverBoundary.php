<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\Exception\ObservationPointNotFoundInBoundaryException;
use Inowas\Common\Id\ObservationPointId;

class RiverBoundary extends ModflowBoundary
{
    const CARDINALITY = 'n';
    const TYPE = 'riv';

    public function addRiverStageToObservationPoint(ObservationPointId $observationPointId, RiverDateTimeValue $riverStage): ModflowBoundary
    {
        if (! $this->hasObservationPoint($observationPointId)){
            throw ObservationPointNotFoundInBoundaryException::withIds($observationPointId);
        }

        $this->addDateTimeValue($riverStage, $observationPointId);
        return $this->self();
    }
}
