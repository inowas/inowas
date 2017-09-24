<?php

namespace Inowas\Common\Boundaries;


use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Modflow\Name;
use Inowas\ModflowBundle\Exception\InvalidArgumentException;

class BoundaryFactory
{
    /** @noinspection MoreThanThreeArgumentsInspection
     * @param BoundaryType $type
     * @param Name $name
     * @param Geometry $geometry
     * @param AffectedLayers $affectedLayers
     * @param Metadata $metadata
     * @return ModflowBoundary
     * @throws InvalidArgumentException
     */
    public static function create(
        BoundaryType $type,
        Name $name,
        Geometry $geometry,
        AffectedLayers $affectedLayers,
        Metadata $metadata
    ): ModflowBoundary
    {
        switch ($type->toString()) {
            case BoundaryType::CONSTANT_HEAD:
                return ConstantHeadBoundary::createWithParams(
                    $name,
                    $geometry,
                    $affectedLayers,
                    $metadata
                );
                break;

            case BoundaryType::GENERAL_HEAD:
                return GeneralHeadBoundary::createWithParams(
                    $name,
                    $geometry,
                    $affectedLayers,
                    $metadata
                );
                break;

            case BoundaryType::RECHARGE:
                return RechargeBoundary::createWithParams(
                    $name,
                    $geometry,
                    $affectedLayers,
                    $metadata
                );
                break;

            case BoundaryType::RIVER:
                return RiverBoundary::createWithParams(
                    $name,
                    $geometry,
                    $affectedLayers,
                    $metadata
                );
                break;

            case BoundaryType::WELL:
                return WellBoundary::createWithParams(
                    $name,
                    $geometry,
                    $affectedLayers,
                    $metadata
                );
                break;
        }

        throw InvalidArgumentException::withMessage(
            sprintf('BoundaryType %s not known', $type->toString())
        );
    }

    public static function createFromArray(array $arr): ModflowBoundary
    {
        $type = BoundaryType::fromString($arr['type']);

        switch ($type->toString()) {
            case BoundaryType::CONSTANT_HEAD:
                return ConstantHeadBoundary::fromArray($arr);
                break;

            case BoundaryType::GENERAL_HEAD:
                return GeneralHeadBoundary::fromArray($arr);
                break;

            case BoundaryType::RECHARGE:
                return RechargeBoundary::fromArray($arr);
                break;

            case BoundaryType::RIVER:
                return RiverBoundary::fromArray($arr);
                break;

            case BoundaryType::WELL:
                return WellBoundary::fromArray($arr);
                break;
        }

        throw InvalidArgumentException::withMessage(
            sprintf('BoundaryType %s not known', $type->toString())
        );
    }
}
