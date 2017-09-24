<?php

declare(strict_types=1);

namespace Tests\Inowas\Common\Boundaries;

use Inowas\Common\Boundaries\BoundaryFactory;
use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\Boundaries\ConstantHeadBoundary;
use Inowas\Common\Boundaries\ConstantHeadDateTimeValue;
use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\ObservationPoint;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Geometry\LineString;
use Inowas\Common\Geometry\Point;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Id\ObservationPointId;
use Inowas\Common\Modflow\Name;

class ConstantHeadBoundaryTest extends \PHPUnit_Framework_TestCase
{

    /** @var  ConstantHeadBoundary */
    protected $constantHeadBoundary;

    public function setUp(): void
    {
        /** @var ConstantHeadBoundary $chd */
        $chd = ConstantHeadBoundary::createWithParams(
            Name::fromString('ChdName'),
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
            BoundaryType::fromString(BoundaryType::CONSTANT_HEAD),
            Name::fromString('OP 1'),
            new Point(-63.8,-31.8, 4326)
        );

        $chd->addObservationPoint($observationPoint);

        $chd->addConstantHeadToObservationPoint(
            $observationPointId,
            ConstantHeadDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
                401, 301
            )
        );

        $chd->addConstantHeadToObservationPoint(
            $observationPointId,
            ConstantHeadDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-01')),
                402, 302
            )
        );

        $chd->addConstantHeadToObservationPoint(
            $observationPointId,
            ConstantHeadDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-01')),
                403, 303
            )
        );

        $chd->addConstantHeadToObservationPoint(
            $observationPointId,
            ConstantHeadDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-04-01')),
                404, 304
            )
        );

        $observationPointId = ObservationPointId::fromString('op2');
        $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
            $observationPointId,
            BoundaryType::fromString(BoundaryType::CONSTANT_HEAD),
            Name::fromString('OP 2'),
            new Point(-63.67,-31.36, 4326)
        );

        $chd->addObservationPoint($observationPoint);

        $chd->addConstantHeadToObservationPoint(
            $observationPointId,
            ConstantHeadDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
                401, 301
            )
        );

        $chd->addConstantHeadToObservationPoint(
            $observationPointId,
            ConstantHeadDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-01')),
                402, 302
            )
        );

        $chd->addConstantHeadToObservationPoint(
            $observationPointId,
            ConstantHeadDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-01')),
                403, 303
            )
        );

        $chd->addConstantHeadToObservationPoint(
            $observationPointId,
            ConstantHeadDateTimeValue::fromParams(
                DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-04-01')),
                404, 304
            )
        );

        $this->constantHeadBoundary = $chd;
    }

    public function test_find_value_by_date_time(): void
    {
        $observationPointId = ObservationPointId::fromString('op1');
        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2014-01-15'));
        $value = $this->constantHeadBoundary->dateTimeValues($observationPointId)->findValueByDateTime($dateTime);
        $this->assertNull($value);

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-15'));
        $value = $this->constantHeadBoundary->dateTimeValues($observationPointId)->findValueByDateTime($dateTime);
        $this->assertInstanceOf(ConstantHeadDateTimeValue::class, $value);
        $this->assertEquals([401, 301], $value->toArray()['values']);

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-15'));
        $value = $this->constantHeadBoundary->dateTimeValues($observationPointId)->findValueByDateTime($dateTime);
        $this->assertInstanceOf(ConstantHeadDateTimeValue::class, $value);
        $this->assertEquals([402, 302], $value->toArray()['values']);

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-15'));
        $value = $this->constantHeadBoundary->dateTimeValues($observationPointId)->findValueByDateTime($dateTime);
        $this->assertInstanceOf(ConstantHeadDateTimeValue::class, $value);
        $this->assertEquals([403, 303], $value->toArray()['values']);

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-04-15'));
        $value = $this->constantHeadBoundary->dateTimeValues($observationPointId)->findValueByDateTime($dateTime);
        $this->assertInstanceOf(ConstantHeadDateTimeValue::class, $value);
        $this->assertEquals([404, 304], $value->toArray()['values']);
    }

    public function test_to_array_from_array(): void
    {
        $arr = $this->constantHeadBoundary->toArray();
        $chd = BoundaryFactory::createFromArray($arr);
        $this->assertInstanceOf(ConstantHeadBoundary::class, $chd);
        $this->assertEquals($this->constantHeadBoundary, $chd);
    }
}
