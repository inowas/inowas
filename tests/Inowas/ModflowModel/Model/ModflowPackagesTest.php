<?php

declare(strict_types=1);

namespace tests\Inowas\ModflowCalculation\Model;


use Inowas\Common\Modflow\PackageName;
use Inowas\ModflowModel\Model\ModflowPackages;

class ModflowPackagesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function it_creates_default_packages(): void
    {
        $packages = ModflowPackages::createFromDefaults();
        $this->assertInstanceOf(ModflowPackages::class, $packages);

        /** @var array $packageData */
        $packageData = $packages->packageData();
        $this->assertTrue(is_array($packageData));
        $this->assertArrayHasKey('selected_packages', $packageData);
        $this->assertArrayHasKey('packages', $packageData);

        /** @var  array $selectedPackages */
        $selectedPackages = $packageData['selected_packages'];
        foreach ($selectedPackages as $packageName) {
            $this->assertArrayHasKey($packageName, $packageData['packages']);
        }
    }

    /**
     * @test
     */
    public function it_creates_and_restores_from_array_correctly(): void
    {
        $packages = ModflowPackages::createFromDefaults();
        $arr = $packages->toArray();
        $restoredPackages = ModflowPackages::fromArray($arr);
        $this->assertEquals($packages, $restoredPackages);
    }

    /**
     * @test
     */
    public function it_serializes_and_unserializes_correctly(): void
    {
        $packages = ModflowPackages::createFromDefaults();
        $json = json_encode($packages->toArray());
        $restoredPackages = ModflowPackages::fromArray(json_decode($json, true));
        $this->assertEquals($packages, $restoredPackages);
    }

    /**
     * @test
     */
    public function it_generates_the_same_hash_after_updating_a_package_with_same_value(): void
    {
        $packages = ModflowPackages::createFromDefaults();
        $hash = $packages->hash();
        $packages->changeFlowPackage(PackageName::fromString('upw'));
        $packages->changeFlowPackage(PackageName::fromString('lpf'));
        $this->assertEquals($hash, $packages->hash());
    }

}
