<?php

namespace Inowas\Common\Boundaries;

use Inowas\Common\DateTime\DateTime;
use Inowas\ModflowBundle\Exception\InvalidArgumentException;

class DateTimeValueFactory
{

    public static function create(BoundaryType $type, DateTime $start): DateTimeValue
    {
        switch ($type->toString()) {
            case (BoundaryType::CONSTANT_HEAD):
                return ConstantHeadDateTimeValue::fromParams($start, 0,0);
                break;
            case (BoundaryType::GENERAL_HEAD):
                return GeneralHeadDateTimeValue::fromParams($start, 0,0);
                break;
            case (BoundaryType::RECHARGE):
                return RechargeDateTimeValue::fromParams($start, 0);
                break;
            case (BoundaryType::RIVER):
                return RiverDateTimeValue::fromParams($start, 0,0, 0);
                break;
            case (BoundaryType::WELL):
                return WellDateTimeValue::fromParams($start, 0);
                break;
        }

        throw InvalidArgumentException::withMessage(
            sprintf('BoundaryType %s not known', $type->toString())
        );
    }

    public static function createFromArray(BoundaryType $type, array $arr): DateTimeValue
    {
        switch ($type->toString()) {
            case (BoundaryType::CONSTANT_HEAD):
                return ConstantHeadDateTimeValue::fromArray($arr);
                break;
            case (BoundaryType::GENERAL_HEAD):
                return GeneralHeadDateTimeValue::fromArray($arr);
                break;
            case (BoundaryType::RECHARGE):
                return RechargeDateTimeValue::fromArray($arr);
                break;
            case (BoundaryType::RIVER):
                return RiverDateTimeValue::fromArray($arr);
                break;
            case (BoundaryType::WELL):
                return WellDateTimeValue::fromArray($arr);
                break;
        }

        throw InvalidArgumentException::withMessage(
            sprintf('BoundaryType %s not known', $type->toString())
        );
    }
}
