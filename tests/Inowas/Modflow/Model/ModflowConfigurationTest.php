<?php

namespace Tests\Inowas\Modflow\Model\Packages;

use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\Distance;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Modflow\PackageName;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\Common\Modflow\Unitnumber;
use Inowas\ModflowModel\Model\ModflowPackages;

class ModflowConfigurationTest extends \PHPUnit_Framework_TestCase
{

    public function test_create_from_defaults(): void
    {
        $packages = ModflowPackages::createFromDefaults();
        $this->assertInstanceOf(ModflowPackages::class, $packages);
    }

    public function test_serialize_packages(): void
    {
        $packages = ModflowPackages::createFromDefaults();
        $json = json_encode($packages);
        $this->assertJson($json);
    }

    public function test_create_from_array(): void
    {
        $packages = ModflowPackages::createFromDefaults();
        $json = json_encode($packages);
        $this->assertJson($json);

        $packages = ModflowPackages::fromArray(json_decode($json, true));
        $this->assertInstanceOf(ModflowPackages::class, $packages);
    }

    public function test_update_default_time_unit(): void
    {
        $packages = ModflowPackages::createFromDefaults();
        $packages->updateTimeUnit(TimeUnit::fromInt(TimeUnit::SECONDS));
        $json = json_encode($packages);
        $this->assertJson($json);
        $arr = json_decode($json, true);
        $this->assertEquals(1, $arr['packages']['dis']['itmuni']);
    }

    public function test_update_time_unit_with_update_param_function(): void
    {
        $packages = ModflowPackages::createFromDefaults();
        $packages->updatePackageParameter('dis', 'TimeUnit', TimeUnit::fromInt(TimeUnit::MINUTES));
        $json = json_encode($packages);
        $this->assertJson($json);
        $arr = json_decode($json, true);
        $this->assertEquals(2, $arr['packages']['dis']['itmuni']);
    }

    public function test_gridsize_has_same_size_as_ibound(): void
    {
        $gridsize = GridSize::fromXY(40,50);
        $boundingBox = BoundingBox::fromCoordinates(10,20,30,40);
        $packages = ModflowPackages::createFromDefaults();
        $dx = Distance::fromMeters(1000);
        $dy = Distance::fromMeters(10000);

        $packages->updateGridParameters($gridsize, $boundingBox, $dx, $dy);
    }

    public function test_change_flow_package(): void
    {
        $packages = ModflowPackages::createFromDefaults();
        $packages->changeFlowPackage(PackageName::fromString('upw'));
        json_encode($packages);
    }

    public function test_remove_package(): void
    {
        $packages = ModflowPackages::createFromDefaults();
        $packages->updatePackageParameter('wel', 'unitnumber', Unitnumber::fromValue(10));
        $this->assertTrue($packages->isSelected(PackageName::fromString('wel')));
        $packages->unSelectPackage(PackageName::fromString('wel'));
        $this->assertFalse($packages->isSelected(PackageName::fromString('wel')));
    }
}
