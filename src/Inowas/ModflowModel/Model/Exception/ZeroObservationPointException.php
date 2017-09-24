<?php

namespace Inowas\ModflowModel\Model\Exception;

use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\Id\BoundaryId;

final class ZeroObservationPointException extends \InvalidArgumentException
{
    public static function withBoundaryIdAndType(BoundaryId $id, BoundaryType $type)
    {
        return new self(sprintf('%s-Boundary with id: %s has no ObservationPoint set', $id->toString(), $type->toString()));
    }
}
