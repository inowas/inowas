<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Command;

use Inowas\Common\Command\AbstractJsonSchemaCommand;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Geometry\Polygon;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\LengthUnit;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\Common\Status\Visibility;

class CreateModflowModel extends AbstractJsonSchemaCommand
{
    /** @noinspection MoreThanThreeArgumentsInspection
     * @param UserId $userId
     * @param ModflowId $modelId
     * @param Name $name
     * @param Description $description
     * @param Polygon $polygon
     * @param GridSize $gridSize
     * @param BoundingBox $boundingBox
     * @param TimeUnit $timeUnit
     * @param LengthUnit $lengthUnit
     * @param Visibility $visibility
     * @return CreateModflowModel
     */
    public static function newWithAllParams(
        UserId $userId,
        ModflowId $modelId,
        Name $name,
        Description $description,
        Polygon $polygon,
        GridSize $gridSize,
        BoundingBox $boundingBox,
        TimeUnit $timeUnit,
        LengthUnit $lengthUnit,
        Visibility $visibility
    ): CreateModflowModel
    {
        $self = new static(
            [
                'id' => $modelId->toString(),
                'name' => $name->toString(),
                'description' => $description->toString(),
                'geometry' => Geometry::fromPolygon($polygon)->toArray(),
                'grid_size' => $gridSize->toArray(),
                'bounding_box' => $boundingBox->toArray(),
                'time_unit' => $timeUnit->toInt(),
                'length_unit' => $lengthUnit->toInt(),
                'public' => $visibility->isPublic()
            ]
        );

        /** @var CreateModflowModel $self */
        $self = $self->withAddedMetadata('user_id', $userId->toString());
        return $self;
    }

    public function schema(): string
    {
        return 'file://spec/schema/modflow/modflowModel.json';
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->metadata['user_id']);
    }

    public function modelId(): ModflowId
    {
        return ModflowId::fromString($this->payload['id']);
    }

    public function name(): Name
    {
        return Name::fromString($this->payload['name']);
    }

    public function description(): Description
    {
        return Description::fromString($this->payload['description']);
    }

    public function geometry(): Polygon
    {
        return Geometry::fromArray($this->payload['geometry'])->getPolygon();
    }

    public function boundingBox(): BoundingBox
    {
        return BoundingBox::fromArray($this->payload['bounding_box']);
    }

    public function gridSize(): GridSize
    {
        return GridSize::fromArray($this->payload['grid_size']);
    }

    public function timeUnit(): TimeUnit
    {
        return TimeUnit::fromInt($this->payload['time_unit']);
    }

    public function lengthUnit(): LengthUnit
    {
        return LengthUnit::fromInt($this->payload['length_unit']);
    }

    public function visibility(): Visibility
    {
        return Visibility::fromBool($this->payload['public']);
    }
}
