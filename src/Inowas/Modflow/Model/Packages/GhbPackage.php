<?php

declare(strict_types=1);

namespace Inowas\Modflow\Model\Packages;

use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\Ipakcb;
use Inowas\Common\Modflow\Options;
use Inowas\Common\Modflow\Unitnumber;

class GhbPackage implements PackageInterface
{

    /** @var string  */
    protected $type = 'ghb';

    /** @var  Ipakcb */
    protected $ipakcb;

    /** @var  GhbStressPeriodData */
    protected $stressPeriodData;

    /** @var  Options */
    protected $options;

    /** @var Extension */
    protected $extension;

    /** @var  Unitnumber */
    protected $unitnumber;


    public static function fromDefaults()
    {
        $ipakcb = Ipakcb::fromInteger(0);
        $stressPeriodData = GhbStressPeriodData::create();
        $options = Options::fromValue(null);
        $extension = Extension::fromString('ghb');
        $unitnumber = Unitnumber::fromInteger(23);

        return new self($ipakcb, $stressPeriodData, $options, $extension, $unitnumber);
    }

    public static function fromParams(
        Ipakcb $ipakcb,
        GhbStressPeriodData $GhbStressPeriodData,
        Options $options,
        Extension $extension,
        Unitnumber $unitnumber
    ): GhbPackage
    {
        return new self($ipakcb, $GhbStressPeriodData, $options, $extension, $unitnumber);
    }

    public static function fromArray(array $arr): GhbPackage
    {
        $ipakcb = Ipakcb::fromInteger($arr['ipakcb']);
        $stressPeriodData = GhbStressPeriodData::fromArray((array)$arr['stress_period_data']);
        $options = Options::fromValue(null);
        $extension = Extension::fromValue($arr['extension']);
        $unitnumber = Unitnumber::fromValue($arr['unitnumber']);

        return new self($ipakcb, $stressPeriodData, $options, $extension, $unitnumber);
    }

    private function __construct(
        Ipakcb $ipakcb,
        GhbStressPeriodData $stressPeriodData,
        Options $options,
        Extension $extension,
        Unitnumber $unitnumber
    )
    {
        $this->ipakcb = $ipakcb;
        $this->stressPeriodData = $stressPeriodData;
        $this->options = $options;
        $this->extension = $extension;
        $this->unitnumber = $unitnumber;
    }

    public function updateIpakcb(Ipakcb $ipakcb): GhbPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ipakcb = $ipakcb;
        return $package;
    }

    public function updateStressPeriodData(GhbStressPeriodData $stressPeriodData): GhbPackage
    {
        $package = self::fromArray($this->toArray());
        $package->stressPeriodData = $stressPeriodData;
        return $package;
    }

    public function updateOptions(Options $options): GhbPackage
    {
        $package = self::fromArray($this->toArray());
        $package->options = $options;
        return $package;
    }

    public function updateExtension(Extension $extension): GhbPackage
    {
        $package = self::fromArray($this->toArray());
        $package->extension = $extension;
        return $package;
    }

    public function updateUnitnumber(Unitnumber $unitnumber): GhbPackage
    {
        $package = self::fromArray($this->toArray());
        $package->unitnumber = $unitnumber;
        return $package;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function toArray(): array
    {
        return array(
            "ipakcb" => $this->ipakcb->toInteger(),
            "stress_period_data" => $this->stressPeriodData->toArray(),
            "options" => $this->options->toValue(),
            "extension" => $this->extension->toValue(),
            "unitnumber" => $this->unitnumber->toValue()
        );
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return $this->toArray();
    }
}
