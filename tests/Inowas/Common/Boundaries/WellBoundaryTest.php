<?php

declare(strict_types=1);

namespace Tests\Inowas\Common\Boundaries;

use Inowas\Common\Boundaries\BoundaryFactory;
use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\WellBoundary;
use Inowas\Common\Boundaries\WellDateTimeValue;
use Inowas\Common\Boundaries\WellType;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Geometry\Point;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Modflow\Name;

class WellBoundaryTest extends \PHPUnit_Framework_TestCase
{

    /** @var  WellBoundary */
    protected $wellBoundary;

    public function setUp(): void
    {
        /** @var WellBoundary $wb */
        $wb = WellBoundary::createWithParams(
            Name::fromString('WellName'),
            Geometry::fromPoint(new Point(10, 12)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()->addWellType(WellType::fromString(WellType::TYPE_PUBLIC_WELL))
        );

        $wb = $wb->addPumpingRate(WellDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
            1100
        ));

        $wb = $wb->addPumpingRate(WellDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-01')),
            1200
        ));

        $wb = $wb->addPumpingRate(WellDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-01')),
            1300
        ));

        $wb = $wb->addPumpingRate(WellDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-04-01')),
            1400
        ));

        $this->wellBoundary = $wb;
    }

    public function test_find_value_by_date_time(): void
    {
        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2014-01-15'));
        $value = $this->wellBoundary->findValueByDateTime($dateTime);
        $this->assertInstanceOf(WellDateTimeValue::class, $value);
        $this->assertEquals(0, $value->pumpingRate());

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-15'));
        $value = $this->wellBoundary->findValueByDateTime($dateTime);
        $this->assertInstanceOf(WellDateTimeValue::class, $value);
        $this->assertEquals(1100, $value->pumpingRate());

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-15'));
        $value = $this->wellBoundary->findValueByDateTime($dateTime);
        $this->assertInstanceOf(WellDateTimeValue::class, $value);
        $this->assertEquals(1200, $value->pumpingRate());

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-15'));
        $value = $this->wellBoundary->findValueByDateTime($dateTime);
        $this->assertInstanceOf(WellDateTimeValue::class, $value);
        $this->assertEquals(1300, $value->pumpingRate());

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-04-15'));
        $value = $this->wellBoundary->findValueByDateTime($dateTime);
        $this->assertInstanceOf(WellDateTimeValue::class, $value);
        $this->assertEquals(1400, $value->pumpingRate());

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-05-15'));
        $value = $this->wellBoundary->findValueByDateTime($dateTime);
        $this->assertInstanceOf(WellDateTimeValue::class, $value);
        $this->assertEquals(1400, $value->pumpingRate());
    }

    public function test_to_array_from_array(): void
    {
        $arr = $this->wellBoundary->toArray();
        $wb = BoundaryFactory::createFromArray($arr);
        $this->assertInstanceOf(WellBoundary::class, $wb);
        $this->assertEquals($this->wellBoundary, $wb);
    }
}
