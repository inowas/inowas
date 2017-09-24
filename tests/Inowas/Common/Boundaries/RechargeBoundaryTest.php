<?php

declare(strict_types=1);

namespace Tests\Inowas\Common\Boundaries;

use Inowas\Common\Boundaries\BoundaryFactory;
use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\RechargeBoundary;
use Inowas\Common\Boundaries\RechargeDateTimeValue;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Geometry\Polygon;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Modflow\Name;

class RechargeBoundaryTest extends \PHPUnit_Framework_TestCase
{

    /** @var  RechargeBoundary */
    protected $rechargeBoundary;

    public function setUp(): void
    {
        /** @var RechargeBoundary $rch */
        $rch = RechargeBoundary::createWithParams(
            Name::fromString('RechargeName'),

            Geometry::fromPolygon(new Polygon([[
                [-63.687336, -31.313615],
                [-63.687336, -31.367449],
                [-63.569260, -31.367449],
                [-63.569260, -31.313615],
                [-63.687336, -31.313615]
            ]], 4326)),
            AffectedLayers::createWithLayerNumber(LayerNumber::fromInt(0)),
            Metadata::create()
        );

        $rch = $rch->addRecharge(RechargeDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-01')),
            11
        ));

        $rch = $rch->addRecharge(RechargeDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-01')),
            12
        ));

        $rch = $rch->addRecharge(RechargeDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-01')),
            13
        ));

        $rch = $rch->addRecharge(RechargeDateTimeValue::fromParams(
            DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-04-01')),
            14
        ));

        $this->rechargeBoundary = $rch;
    }

    public function test_find_value_by_date_time(): void
    {
        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2014-01-15'));
        $value = $this->rechargeBoundary->findValueByDateTime($dateTime);
        $this->assertInstanceOf(RechargeDateTimeValue::class, $value);
        $this->assertEquals(0, $value->rechargeRate());

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-01-15'));
        $value = $this->rechargeBoundary->findValueByDateTime($dateTime);
        $this->assertInstanceOf(RechargeDateTimeValue::class, $value);
        $this->assertEquals(11, $value->rechargeRate());

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-02-15'));
        $value = $this->rechargeBoundary->findValueByDateTime($dateTime);
        $this->assertInstanceOf(RechargeDateTimeValue::class, $value);
        $this->assertEquals(12, $value->rechargeRate());

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-03-15'));
        $value = $this->rechargeBoundary->findValueByDateTime($dateTime);
        $this->assertInstanceOf(RechargeDateTimeValue::class, $value);
        $this->assertEquals(13, $value->rechargeRate());

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-04-15'));
        $value = $this->rechargeBoundary->findValueByDateTime($dateTime);
        $this->assertInstanceOf(RechargeDateTimeValue::class, $value);
        $this->assertEquals(14, $value->rechargeRate());

        $dateTime = DateTime::fromDateTimeImmutable(new \DateTimeImmutable('2015-05-15'));
        $value = $this->rechargeBoundary->findValueByDateTime($dateTime);
        $this->assertInstanceOf(RechargeDateTimeValue::class, $value);
        $this->assertEquals(14, $value->rechargeRate());
    }

    public function test_to_array_from_array(): void
    {
        $arr = $this->rechargeBoundary->toArray();
        $rch = BoundaryFactory::createFromArray($arr);
        $this->assertInstanceOf(RechargeBoundary::class, $rch);
        $this->assertEquals($this->rechargeBoundary, $rch);
    }
}
