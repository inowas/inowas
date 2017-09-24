<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\Ipakcb;
use Inowas\Common\Modflow\Options;
use Inowas\Common\Modflow\Unitnumber;

class WelPackage implements PackageInterface
{

    /** @var string  */
    protected $type = 'wel';

    /** @var  Ipakcb */
    protected $ipakcb;

    /** @var  WelStressPeriodData */
    protected $stressPeriodData;

    /** @var  Options */
    protected $options;

    /** @var Extension */
    protected $extension;

    /** @var  Unitnumber */
    protected $unitnumber;


    public static function fromDefaults(): WelPackage
    {
        $ipakcb = Ipakcb::fromInteger(0);
        $stressPeriodData = WelStressPeriodData::create();
        $options = Options::fromValue(null);
        $extension = Extension::fromString('wel');
        $unitnumber = Unitnumber::fromInteger(20);

        return new self($ipakcb, $stressPeriodData, $options, $extension, $unitnumber);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param Ipakcb $ipakcb
     * @param WelStressPeriodData $welStressPeriodData
     * @param Options $options
     * @param Extension $extension
     * @param Unitnumber $unitnumber
     * @return WelPackage
     */
    public static function fromParams(
        Ipakcb $ipakcb,
        WelStressPeriodData $welStressPeriodData,
        Options $options,
        Extension $extension,
        Unitnumber $unitnumber
    ): WelPackage
    {
        return new self($ipakcb, $welStressPeriodData, $options, $extension, $unitnumber);
    }

    public static function fromArray(array $arr): WelPackage
    {
        $ipakcb = Ipakcb::fromInteger($arr['ipakcb']);
        $stressPeriodData = WelStressPeriodData::fromArray((array)$arr['stress_period_data']);
        $options = Options::fromValue(null);
        $extension = Extension::fromValue($arr['extension']);
        $unitnumber = Unitnumber::fromValue($arr['unitnumber']);

        return new self($ipakcb, $stressPeriodData, $options, $extension, $unitnumber);
    }

    private function __construct(
        Ipakcb $ipakcb,
        WelStressPeriodData $stressPeriodData,
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

    public function updateIpakcb(Ipakcb $ipakcb): WelPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ipakcb = $ipakcb;
        return $package;
    }

    public function updateStressPeriodData(WelStressPeriodData $stressPeriodData): WelPackage
    {
        $package = self::fromArray($this->toArray());
        $package->stressPeriodData = $stressPeriodData;
        return $package;
    }

    public function updateOptions(Options $options): WelPackage
    {
        $package = self::fromArray($this->toArray());
        $package->options = $options;
        return $package;
    }

    public function updateExtension(Extension $extension): WelPackage
    {
        $package = self::fromArray($this->toArray());
        $package->extension = $extension;
        return $package;
    }

    public function updateUnitnumber(Unitnumber $unitnumber): WelPackage
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
