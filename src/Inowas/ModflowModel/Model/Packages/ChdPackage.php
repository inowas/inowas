<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\Unitnumber;

class ChdPackage implements PackageInterface
{

    /** @var string  */
    protected $type = 'chd';

    /** @var  ChdStressPeriodData */
    protected $stressPeriodData;

    /** @var Extension */
    protected $extension;

    /** @var  Unitnumber */
    protected $unitnumber;


    public static function fromDefaults(): ChdPackage
    {
        $stressPeriodData = ChdStressPeriodData::create();
        $extension = Extension::fromString('chd');
        $unitnumber = Unitnumber::fromInteger(24);

        return new self($stressPeriodData, $extension, $unitnumber);
    }

    public static function fromParams(
        ChdStressPeriodData $stressPeriodData,
        Extension $extension,
        Unitnumber $unitnumber
    ): ChdPackage
    {
        return new self($stressPeriodData, $extension, $unitnumber);
    }

    public static function fromArray(array $arr): ChdPackage
    {
        $stressPeriodData = ChdStressPeriodData::fromArray((array)$arr['stress_period_data']);
        $extension = Extension::fromValue($arr['extension']);
        $unitnumber = Unitnumber::fromValue($arr['unitnumber']);

        return new self($stressPeriodData, $extension, $unitnumber);
    }

    private function __construct(
        ChdStressPeriodData $stressPeriodData,
        Extension $extension,
        Unitnumber $unitnumber
    )
    {
        $this->stressPeriodData = $stressPeriodData;
        $this->extension = $extension;
        $this->unitnumber = $unitnumber;
    }

    public function updateStressPeriodData(ChdStressPeriodData $stressPeriodData): ChdPackage
    {
        $package = self::fromArray($this->toArray());
        $package->stressPeriodData = $stressPeriodData;
        return $package;
    }

    public function updateExtension(Extension $extension): ChdPackage
    {
        $package = self::fromArray($this->toArray());
        $package->extension = $extension;
        return $package;
    }

    public function updateUnitnumber(Unitnumber $unitnumber): ChdPackage
    {
        $package = self::fromArray($this->toArray());
        $package->unitnumber = $unitnumber;
        return $package;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function isValid(): bool
    {
        return $this->stressPeriodData->hasData();
    }

    public function toArray(): array
    {
        return array(
            'stress_period_data' => (object)$this->stressPeriodData->toArray(),
            'extension' => $this->extension->toValue(),
            'unitnumber' => $this->unitnumber->toValue()
        );
    }

    public function getEditables(): array
    {
        return $this->toArray();
    }

    public function mergeEditables(array $arr): void
    {}

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
