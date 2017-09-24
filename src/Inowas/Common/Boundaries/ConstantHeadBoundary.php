<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\Id\ObservationPointId;

class ConstantHeadBoundary extends ModflowBoundary
{
    const CARDINALITY = 'n';
    const TYPE = 'chd';

    public function addConstantHeadToObservationPoint(ObservationPointId $observationPointId, ConstantHeadDateTimeValue $chdTimeValue): ModflowBoundary
    {
        $this->addDateTimeValue($chdTimeValue, $observationPointId);
        return $this->self();
    }
}
