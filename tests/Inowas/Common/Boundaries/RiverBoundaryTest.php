<?php

declare(strict_types=1);

namespace Tests\Inowas\Common\Boundaries;

use Inowas\Common\Boundaries\BoundaryFactory;
use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\ObservationPoint;
use Inowas\Common\Boundaries\RiverBoundary;
use Inowas\Common\Boundaries\RiverDateTimeValue;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Geometry\LineString;
use Inowas\Common\Geometry\Point;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Id\ObservationPointId;
use Inowas\Common\Modflow\Name;

class RiverBoundaryTest extends \PHPUnit_Framework_TestCase
{

    /** @var  RiverBoundary */
    protected $riverBoundary;

    public function setUp(): void
    {
        /** @var RiverBoundary $riv */
        $riv = RiverBoundary::createWithParams(
            Name::fromString('RiverName'),
            Geometry::fromLineString(new LineString([
                [-63.687336, -31.313615],
                [-63.687336, -31.367449],
                [-63.569260, -31.367449],
                [-63.569260, -31.313615],
                [-63.687336, -31.313615]
            ], 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()
        );

        $observationPointId = ObservationPointId::fromString('op1');
        $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
            $observationPointId,
            BoundaryType::fromString(BoundaryType::RIVER),
            Name::fromString('OP 1'),
            new Point(-63.8,-31.8, 4326)
        );

        $riv->addObservationPoint($observationPoint);

        $riv->addRiverStageToObservationPoint(
            $observationPointId,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
                401, 301, 201
            )
        );

        $riv->addRiverStageToObservationPoint(
            $observationPointId,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-01')),
                402, 302, 202
            )
        );

        $riv->addRiverStageToObservationPoint(
            $observationPointId,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-01')),
                403, 303, 203
            )
        );

        $riv->addRiverStageToObservationPoint(
            $observationPointId,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-04-01')),
                404, 304, 204
            )
        );

        $observationPointId = ObservationPointId::fromString('op2');
        $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
            $observationPointId,
            BoundaryType::fromString(BoundaryType::RIVER),
            Name::fromString('OP 2'),
            new Point(-63.67,-31.36, 4326)
        );

        $riv->addObservationPoint($observationPoint);

        $riv->addRiverStageToObservationPoint(
            $observationPointId,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
                401, 301, 201
            )
        );

        $riv->addRiverStageToObservationPoint(
            $observationPointId,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-01')),
                402, 302, 202
            )
        );

        $riv->addRiverStageToObservationPoint(
            $observationPointId,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-01')),
                403, 303, 203
            )
        );

        $riv->addRiverStageToObservationPoint(
            $observationPointId,
            RiverDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-04-01')),
                404, 304, 204
            )
        );

        $this->riverBoundary = $riv;
    }

    public function test_find_value_by_date_time(): void
    {
        $observationPointId = ObservationPointId::fromString('op1');
        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2014-01-15'));
        $value = $this->riverBoundary->dateTimeValues($observationPointId)->findValueByDateTime($dateTime);
        $this->assertNull($value);

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-15'));
        $value = $this->riverBoundary->dateTimeValues($observationPointId)->findValueByDateTime($dateTime);
        $this->assertInstanceOf(RiverDateTimeValue::class, $value);
        $this->assertEquals([401, 301, 201], $value->toArray()['values']);

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-15'));
        $value = $this->riverBoundary->dateTimeValues($observationPointId)->findValueByDateTime($dateTime);
        $this->assertInstanceOf(RiverDateTimeValue::class, $value);
        $this->assertEquals([402, 302, 202], $value->toArray()['values']);

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-15'));
        $value = $this->riverBoundary->dateTimeValues($observationPointId)->findValueByDateTime($dateTime);
        $this->assertInstanceOf(RiverDateTimeValue::class, $value);
        $this->assertEquals([403, 303, 203], $value->toArray()['values']);

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-04-15'));
        $value = $this->riverBoundary->dateTimeValues($observationPointId)->findValueByDateTime($dateTime);
        $this->assertInstanceOf(RiverDateTimeValue::class, $value);
        $this->assertEquals([404, 304, 204], $value->toArray()['values']);
    }

    public function test_to_array_from_array(): void
    {
        $arr = $this->riverBoundary->toArray();
        $riv = BoundaryFactory::createFromArray($arr);
        $this->assertInstanceOf(RiverBoundary::class, $riv);
        $this->assertEquals($this->riverBoundary, $riv);
    }
}
