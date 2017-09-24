<?php

declare(strict_types=1);

namespace Inowas\Common\Exception;

use Inowas\Common\Id\ObservationPointId;

class ObservationPointNotFoundInBoundaryException extends \InvalidArgumentException
{
    public static function withIds(ObservationPointId $observationPointId): ObservationPointNotFoundInBoundaryException
    {
        return new self(sprintf('The ObservationPoint with Key %s cannot be found in Boundary.', $observationPointId->toString()));
    }
}
