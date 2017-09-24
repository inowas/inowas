<?php

namespace Tests\Inowas\Modflow\Model\Packages;

use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Grid\Distance;
use Inowas\Common\Grid\Nlay;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\Ncol;
use Inowas\Common\Grid\Delc;
use Inowas\Common\Grid\Delr;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Grid\Laycbd;
use Inowas\Common\Grid\Nrow;
use Inowas\Common\Grid\Proj4str;
use Inowas\Common\Grid\Rotation;
use Inowas\Common\Modflow\Botm;
use Inowas\Common\Modflow\Top;
use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\LengthUnit;
use Inowas\Common\Modflow\Nstp;
use Inowas\Common\Modflow\Steady;
use Inowas\Common\Modflow\Perlen;
use Inowas\Common\Modflow\Nper;
use Inowas\Common\Modflow\Tsmult;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\Common\Modflow\Unitnumber;
use Inowas\Common\Modflow\Xul;
use Inowas\Common\Modflow\Yul;
use Inowas\ModflowModel\Model\Packages\DisPackage;


class DisPackageTest extends \PHPUnit_Framework_TestCase
{
    public function test_create_from_params(){

        // DEFAULTS
        $nLay = Nlay::fromInt(1);
        $nCol = Ncol::fromInt(1);
        $nRow = Nrow::fromInt(1);
        $nPer = Nper::fromInteger(1);
        $delR = Delr::fromValue(1.0);
        $delC = Delc::fromValue(1.0);
        $layCbd = Laycbd::fromValue(0);
        $top = Top::fromValue(1.0);
        $botm = Botm::fromValue(0);
        $perlen = Perlen::fromValue(1.0);
        $nstp = Nstp::fromInt(1);
        $tsMult = Tsmult::fromValue(1.0);
        $steady = Steady::fromValue(true);
        $itmUni = TimeUnit::fromInt(TimeUnit::DAYS);
        $lenUni = LengthUnit::fromInt(LengthUnit::METERS);
        $extension = Extension::fromString('dis');
        $unitNumber = Unitnumber::fromInteger(11);
        $xul = Xul::fromValue(null);
        $yul = Yul::fromValue(null);
        $rotation = Rotation::fromFloat(0.0);
        $proj4Str = Proj4str::fromString('EPSG:4326');
        $startDateTime = DateTime::fromDateTime(new \DateTime('1/1/1970'));

        $disPackage = DisPackage::fromParams(
            $nLay, $nRow, $nCol, $nPer, $delR, $delC, $layCbd, $top,
            $botm, $perlen, $nstp, $tsMult, $steady, $itmUni, $lenUni,
            $extension, $unitNumber, $xul, $yul, $rotation, $proj4Str, $startDateTime
        );

        $this->assertInstanceOf(DisPackage::class, $disPackage);
        $json = json_encode($disPackage);
        $this->assertJson($json);
    }

    public function test_create_from_defaults(){
        $disPackage = DisPackage::fromDefaults();
        $this->assertInstanceOf(DisPackage::class, $disPackage);
        $json = json_encode($disPackage);
        $this->assertJson($json);
    }

    public function test_update_time_length_units(){

        /** @var DisPackage $disPackage */
        $disPackage = DisPackage::fromDefaults();
        $this->assertEquals(TimeUnit::fromValue(TimeUnit::DAYS), $disPackage->itmuni());
        $this->assertEquals(LengthUnit::fromValue(LengthUnit::METERS), $disPackage->lenuni());

        $newTimeUnit = TimeUnit::fromValue(TimeUnit::SECONDS);
        $disPackage = $disPackage->updateTimeUnit($newTimeUnit);
        $this->assertEquals($newTimeUnit, $disPackage->itmuni());

        $newLengthUnit = LengthUnit::fromValue(LengthUnit::FEET);
        $disPackage = $disPackage->updateLengthUnit($newLengthUnit);
        $this->assertEquals($newLengthUnit, $disPackage->lenuni());
    }

    public function test_update_grid_parameters_units(){

        /** @var DisPackage $disPackage */
        $disPackage = DisPackage::fromDefaults();
        $boundingBox = BoundingBox::fromCoordinates(1,2,3,4);
        $gridSize = GridSize::fromXY(7, 12);
        $disPackage = $disPackage->updateGridParameters($gridSize, $boundingBox, Distance::fromMeters(700), Distance::fromMeters(1200));

        $expectedNRow = Nrow::fromInt(12);
        $expectedNCol = Ncol::fromInt(7);
        $expectedDelR = Delr::fromValue(100);
        $expectedDelC = Delc::fromValue(100);
        $expectedXul = Xul::fromValue(1);
        $expectedYul = Yul::fromValue(4);

        $this->assertEquals($expectedNRow, $disPackage->nRow());
        $this->assertEquals($expectedNCol, $disPackage->nCol());
        $this->assertEquals($expectedDelR, $disPackage->delR());
        $this->assertEquals($expectedDelC, $disPackage->delCol());
        $this->assertEquals($expectedXul, $disPackage->xul());
        $this->assertEquals($expectedYul, $disPackage->yul());
        $json = json_encode($disPackage);
        $this->assertJson($json);
    }
}
