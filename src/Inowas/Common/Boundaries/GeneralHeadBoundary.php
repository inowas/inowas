<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\Exception\ObservationPointNotFoundInBoundaryException;
use Inowas\Common\Id\ObservationPointId;

class GeneralHeadBoundary extends ModflowBoundary
{
    const CARDINALITY = 'n';
    const TYPE = 'ghb';

    public function addGeneralHeadValueToObservationPoint(ObservationPointId $observationPointId, GeneralHeadDateTimeValue $ghbDateTimeValue): ModflowBoundary
    {
        if (! $this->hasObservationPoint($observationPointId)){
            throw ObservationPointNotFoundInBoundaryException::withIds($observationPointId);
        }

        $this->addDateTimeValue($ghbDateTimeValue, $observationPointId);
        return $this->self();
    }
}
