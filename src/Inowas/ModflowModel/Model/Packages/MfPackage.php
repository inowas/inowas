<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

use Inowas\Common\FileSystem\Externalpath;
use Inowas\Common\FileSystem\NameFileExtension;
use Inowas\Common\FileSystem\FileName;
use Inowas\Common\FileSystem\Modelworkspace;
use Inowas\Common\Modflow\ExecutableName;
use Inowas\Common\Modflow\Listunit;
use Inowas\Common\Modflow\Verbose;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Modflow\Version;

class MfPackage extends AbstractPackage
{
    const TYPE = 'mf';
    const DESCRIPTION = 'Modflow-General Package';

    /** @var  Name */
    protected $modelname;

    /** @var  NameFileExtension */
    protected $nameFileExtension;

    /** @var Version */
    protected $version;

    /** @var ExecutableName */
    protected $executableName;

    /** @var Listunit  */
    protected $listUnit;

    /** @var Modelworkspace */
    protected $modelWorkSpace;

    /** @var Externalpath */
    protected $externalPath;

    /** @var Verbose  */
    protected $verbose;

    public static function fromDefaults(): MfPackage
    {
        $name = Name::fromString('testmodel');
        $fileExtension = NameFileExtension::fromString('nam');
        $version = Version::fromString(Version::MF2005);
        $executableName = FileName::fromString('mf2005');
        $listUnit = Listunit::fromInt(2);
        $modelWorkSpace = Modelworkspace::fromString('.');
        $externalPath = ExternalPath::fromValue(null);
        $verbose = Verbose::fromBool(false);

        return new self($name, $fileExtension, $version, $executableName, $listUnit, $modelWorkSpace, $externalPath, $verbose);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param Name $name
     * @param NameFileExtension $fileExtension
     * @param Version $version
     * @param FileName $executableName
     * @param Listunit $listUnit
     * @param Modelworkspace $modelWorkSpace
     * @param Externalpath $externalPath
     * @param Verbose $verbose
     * @return MfPackage
     */
    public static function fromParams(
        Name $name,
        NameFileExtension $fileExtension,
        Version $version,
        FileName $executableName,
        Listunit $listUnit,
        Modelworkspace $modelWorkSpace,
        ExternalPath $externalPath,
        Verbose $verbose
    ): MfPackage
    {
        return new self($name, $fileExtension, $version, $executableName, $listUnit, $modelWorkSpace, $externalPath, $verbose);
    }

    public static function fromArray(array $arr): MfPackage
    {
        $name = Name::fromString($arr['modelname']);
        $fileExtension = NameFileExtension::fromString($arr['namefile_ext']);
        $version = Version::fromString($arr['version']);
        $executableName = FileName::fromString($arr['exe_name']);
        $listUnit = Listunit::fromInt($arr['listunit']);
        $modelWorkSpace = Modelworkspace::fromString($arr['model_ws']);
        $externalPath = ExternalPath::fromValue($arr['external_path']);
        $verbose = Verbose::fromBool($arr['verbose']);

        return new self($name, $fileExtension, $version, $executableName, $listUnit, $modelWorkSpace, $externalPath, $verbose);
    }

    private function __construct(
        Name $name,
        NameFileExtension $fileExtension,
        Version $version,
        FileName $executableName,
        Listunit $listUnit,
        Modelworkspace $modelWorkSpace,
        ExternalPath $externalPath,
        Verbose $verbose
    )
    {
        $this->modelname = $name;
        $this->nameFileExtension = $fileExtension;
        $this->version = $version;
        $this->executableName = $executableName;
        $this->listUnit = $listUnit;
        $this->modelWorkSpace = $modelWorkSpace;
        $this->externalPath = $externalPath;
        $this->verbose = $verbose;
    }

    public function modelname(): Name
    {
        return $this->modelname;
    }

    public function nameFileExtension(): NameFileExtension
    {
        return $this->nameFileExtension;
    }

    public function version(): Version
    {
        return $this->version;
    }

    public function executableName(): FileName
    {
        return $this->executableName;
    }

    public function listUnit(): Listunit
    {
        return $this->listUnit;
    }

    public function modelWorkSpace(): Modelworkspace
    {
        return $this->modelWorkSpace;
    }

    public function externalPath(): ExternalPath
    {
        return $this->externalPath;
    }

    public function verbose(): Verbose
    {
        return $this->verbose;
    }

    public function toArray(): array
    {
        return array(
            'modelname' => $this->modelname->slugified(),
            'namefile_ext' => $this->nameFileExtension->toString(),
            'version' => $this->version->toString(),
            'exe_name' => $this->executableName->toString(),
            'listunit' => $this->listUnit->toInt(),
            'model_ws' => $this->modelWorkSpace->toString(),
            'external_path' => $this->externalPath->toValue(),
            'verbose' => $this->verbose->toBool()
        );
    }

    public function updateModelname(Name $name): MfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->modelname = $name;
        return $package;
    }

    public function updateNameFileExtension(NameFileExtension $extension): MfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->nameFileExtension = $extension;
        return $package;
    }

    public function updateVersion(Version $version): MfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->version = $version;
        return $package;
    }

    public function updateExecutableName(ExecutableName $executableName): MfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->executableName = $executableName;
        return $package;
    }

    public function updateListunit(Listunit $listunit): MfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->listUnit = $listunit;
        return $package;
    }

    public function updateModelworkspace(Modelworkspace $modelworkspace): MfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->modelWorkSpace = $modelworkspace;
        return $package;
    }

    public function updateExternalPath(ExternalPath $externalPath): MfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->externalPath = $externalPath;
        return $package;
    }

    public function updateVerbose(Verbose $verbose): MfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->verbose = $verbose;
        return $package;
    }

    public function isValid(): bool
    {
        return true;
    }

    public function getEditables(): array
    {
        return $this->toArray();
    }

    public function mergeEditables(array $arr): void
    {}

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
