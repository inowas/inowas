<?php

namespace Inowas\Common\Modflow;

use Inowas\AppBundle\Model\UserPermission;
use Inowas\Common\Geometry\Polygon;
use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Status\Visibility;

final class ModflowModel implements \JsonSerializable
{

    /** @var  ModflowId */
    private $id;

    /** @var  Name */
    private $name;

    /** @var  Description */
    private $description;

    /** @var Polygon */
    private $geometry;

    /** @var BoundingBox */
    private $boundingBox;

    /** @var GridSize */
    private $gridSize;

    /** @var  TimeUnit */
    private $timeUnit;

    /** @var  LengthUnit */
    private $lengthUnit;

    /** @var ActiveCells */
    private $activeCells;

    /** @var UserPermission */
    private $userPermission;

    /** @var Visibility */
    private $visibility;

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param ModflowId $id
     * @param Name $name
     * @param Description $description
     * @param Polygon $geometry
     * @param BoundingBox $boundingBox
     * @param GridSize $gridSize
     * @param TimeUnit $timeUnit
     * @param LengthUnit $lengthUnit
     * @param ActiveCells $activeCells
     * @param UserPermission $userPermission
     * @param Visibility $visibility
     * @return ModflowModel
     */
    public static function fromParams(
        ModflowId $id,
        Name $name,
        Description $description,
        Polygon $geometry,
        BoundingBox $boundingBox,
        GridSize $gridSize,
        TimeUnit $timeUnit,
        LengthUnit $lengthUnit,
        ActiveCells $activeCells,
        UserPermission $userPermission,
        Visibility $visibility
    ): ModflowModel
    {
        $self = new self();
        $self->id = $id;
        $self->name = $name;
        $self->description = $description;
        $self->geometry = $geometry;
        $self->boundingBox = $boundingBox;
        $self->gridSize = $gridSize;
        $self->timeUnit = $timeUnit;
        $self->lengthUnit = $lengthUnit;
        $self->activeCells = $activeCells;
        $self->userPermission = $userPermission;
        $self->visibility = $visibility;
        return $self;
    }

    private function __construct(){}

    public function id(): ModflowId
    {
        return $this->id;
    }

    public function name(): Name
    {
        return $this->name;
    }

    public function description(): Description
    {
        return $this->description;
    }

    public function geometry(): Polygon
    {
        return $this->geometry;
    }

    public function boundingBox(): BoundingBox
    {
        return $this->boundingBox;
    }

    public function gridSize(): GridSize
    {
        return $this->gridSize;
    }

    public function timeUnit(): TimeUnit
    {
        return $this->timeUnit;
    }

    public function lengthUnit(): LengthUnit
    {
        return $this->lengthUnit;
    }

    public function visibility(): Visibility
    {
        return $this->visibility;
    }

    public function activeCells(): ActiveCells
    {
        return $this->activeCells;
    }

    public function toArray(): array
    {
        return array(
            'id' => $this->id->toString(),
            'name' => $this->name->toString(),
            'description' => $this->description->toString(),
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => $this->geometry->toArray()
            ],
            'bounding_box' => $this->boundingBox->toArray(),
            'grid_size' => $this->gridSize->toArray(),
            'time_unit' => $this->timeUnit->toInt(),
            'length_unit' => $this->lengthUnit->toInt(),
            'active_cells' => $this->activeCells->cells2D(),
            'permissions' => $this->userPermission->toString(),
            'public' => $this->visibility->isPublic()
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
