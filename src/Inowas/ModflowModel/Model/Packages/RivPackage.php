<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\Ipakcb;
use Inowas\Common\Modflow\Options;
use Inowas\Common\Modflow\Unitnumber;

class RivPackage implements PackageInterface
{

    /** @var string  */
    protected $type = 'riv';

    /** @var  Ipakcb */
    protected $ipakcb;

    /** @var  RivStressPeriodData */
    protected $stressPeriodData;

    /** @var  Options */
    protected $options;

    /** @var Extension */
    protected $extension;

    /** @var  Unitnumber */
    protected $unitnumber;

    public static function fromDefaults(): RivPackage
    {
        $ipakcb = Ipakcb::fromInteger(0);
        $stressPeriodData = RivStressPeriodData::create();
        $options = Options::fromValue(null);
        $extension = Extension::fromString('riv');
        $unitnumber = Unitnumber::fromInteger(18);

        return new self($ipakcb, $stressPeriodData, $options, $extension, $unitnumber);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param Ipakcb $ipakcb
     * @param RivStressPeriodData $stressPeriodData
     * @param Options $options
     * @param Extension $extension
     * @param Unitnumber $unitnumber
     * @return RivPackage
     */
    public static function fromParams(
        Ipakcb $ipakcb,
        RivStressPeriodData $stressPeriodData,
        Options $options,
        Extension $extension,
        Unitnumber $unitnumber
    ): RivPackage
    {
        return new self($ipakcb, $stressPeriodData, $options, $extension, $unitnumber);
    }

    public static function fromArray(array $arr): RivPackage
    {
        $ipakcb = Ipakcb::fromInteger($arr['ipakcb']);
        $stressPeriodData = RivStressPeriodData::fromArray((array)$arr['stress_period_data']);
        $options = Options::fromValue(null);
        $extension = Extension::fromString($arr['extension']);
        $unitnumber = Unitnumber::fromInteger($arr['unitnumber']);

        return new self($ipakcb, $stressPeriodData, $options, $extension, $unitnumber);
    }

    private function __construct(
        Ipakcb $ipakcb,
        RivStressPeriodData $stressPeriodData,
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

    public function updateIpakcb(Ipakcb $ipakcb): RivPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ipakcb = $ipakcb;
        return $package;
    }

    public function updateStressPeriodData(RivStressPeriodData $stressPeriodData): RivPackage
    {
        $package = self::fromArray($this->toArray());
        $package->stressPeriodData = $stressPeriodData;
        return $package;
    }

    public function updateOptions(Options $options): RivPackage
    {
        $package = self::fromArray($this->toArray());
        $package->options = $options;
        return $package;
    }

    public function updateExtension(Extension $extension): RivPackage
    {
        $package = self::fromArray($this->toArray());
        $package->extension = $extension;
        return $package;
    }

    public function updateUnitnumber(Unitnumber $unitnumber): RivPackage
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
            'ipakcb' => $this->ipakcb->toInteger(),
            'stress_period_data' => (object)$this->stressPeriodData->toArray(),
            'options' => $this->options->toValue(),
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

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
