<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Id\BoundaryId;

class RiverBoundary extends AbstractBoundary
{
    public static function create(BoundaryId $boundaryId): RiverBoundary
    {
        return new self($boundaryId);
    }

    public static function createWithIdNameAndGeometry(
        BoundaryId $boundaryId,
        BoundaryName $name,
        Geometry $geometry
    ): RiverBoundary
    {
        $self = new self($boundaryId, $name, $geometry);
        return $self;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return 'riv';
    }

    /**
     * @return array
     */
    public function metadata(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public function dataToJson(): string
    {
        return json_encode([]);
    }
}
