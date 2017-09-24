<?php

namespace Inowas\ModflowModel\Model\Exception;

use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\Id\BoundaryId;

final class InvalidBoundaryGeometryException extends \InvalidArgumentException
{
    public static function withBoundaryIdAndGeometry(BoundaryId $id, BoundaryType $type, string $expected)
    {
        return new self(sprintf('%s-Boundary with Id: %s does not contain expected Geometry-Type %s', $type->toString(), $id->toString(), $expected));
    }
}
