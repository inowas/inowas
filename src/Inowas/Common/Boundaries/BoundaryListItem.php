<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Modflow\Name;

final class BoundaryListItem
{
    /** @var BoundaryId */
    private $id;

    /** @var Name */
    private $name;

    /** @var Geometry */
    private $geometry;

    /** @var  BoundaryType */
    private $type;

    /** @var  Metadata */
    private $metadata;

    /** @var  AffectedLayers */
    private $affectedLayers;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param BoundaryId $id
     * @param Name $name
     * @param Geometry $geometry
     * @param BoundaryType $type
     * @param Metadata $metadata
     * @param AffectedLayers $affectedLayers
     * @return BoundaryListItem
     */
    public static function fromParams(
        BoundaryId $id,
        Name $name,
        Geometry $geometry,
        BoundaryType $type,
        Metadata $metadata,
        AffectedLayers $affectedLayers
    ): BoundaryListItem
    {
        $self = new self();
        $self->id = $id;
        $self->name = $name;
        $self->geometry = $geometry;
        $self->type = $type;
        $self->metadata = $metadata;
        $self->affectedLayers = $affectedLayers;
        return $self;
    }

    private function __construct()
    {}

    public function toArray(): array
    {
        return [
            'id' => $this->id->toString(),
            'name' => $this->name->toString(),
            'geometry' => $this->geometry->toArray(),
            'type' => $this->type->toString(),
            'metadata' => $this->metadata->toArray(),
            'affected_layers' => $this->affectedLayers->toArray()
        ];
    }
}
