<?php

declare(strict_types=1);

namespace Tests\Inowas\Modflow\Model\Packages;

use Inowas\Common\FileSystem\Externalpath;
use Inowas\Common\FileSystem\NameFileExtension;
use Inowas\Common\FileSystem\FileName;
use Inowas\Common\FileSystem\Modelworkspace;
use Inowas\Common\Modflow\Listunit;
use Inowas\Common\Modflow\Verbose;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Modflow\Version;
use Inowas\ModflowModel\Model\Packages\MfPackage;

class MfPackageTest extends \PHPUnit_Framework_TestCase
{
    public function test_create(){
        $modflowModelName = Name::fromString('ModelName');
        $fileExtension = NameFileExtension::fromString('nam');
        $version = Version::fromString(Version::MF2005);
        $executableName = FileName::fromString('mf2005');
        $listUnit = Listunit::fromInt(2);
        $modelWorkSpace = Modelworkspace::fromString('.');
        $externalPath = Externalpath::fromValue(null);
        $verbose = Verbose::fromBool(false);

        $mfPackage = MfPackage::fromParams(
            $modflowModelName,
            $fileExtension,
            $version,
            $executableName,
            $listUnit,
            $modelWorkSpace,
            $externalPath,
            $verbose
        );

        $this->assertInstanceOf(MfPackage::class, $mfPackage);
        $json = json_encode($mfPackage);
        $this->assertJson($json);
    }

    public function test_create_from_default(){
        $mfPackage = MfPackage::fromDefaults();
        $this->assertInstanceOf(MfPackage::class, $mfPackage);
        $json = json_encode($mfPackage);
        $this->assertJson($json);
    }

    public function test_update_modelname(){
        $mfPackage = MfPackage::fromDefaults();
        $mfPackage = $mfPackage->updateModelname(Name::fromString('modelnametest2'));
        $obj = json_decode(json_encode($mfPackage));
        $this->assertEquals('modelnametest2', $obj->modelname);
    }
}
