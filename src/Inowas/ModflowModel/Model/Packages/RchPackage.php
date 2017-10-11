<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\Ipakcb;
use Inowas\Common\Modflow\Irch;
use Inowas\Common\Modflow\Nrchop;
use Inowas\Common\Modflow\Rech;
use Inowas\Common\Modflow\Unitnumber;

class RchPackage extends AbstractPackage
{
    const TYPE = 'rch';
    const DESCRIPTION = 'Recharge Package';

    /** @var  Ipakcb */
    protected $ipakcb;

    /** @var  Nrchop */
    protected $nrchop;

    /** @var  RchStressPeriodData */
    protected $stressPeriodData;

    /** @var  Irch */
    protected $irch;

    /** @var Extension */
    protected $extension;

    /** @var  Unitnumber */
    protected $unitnumber;

    public static function fromDefaults(): RchPackage
    {
        $ipakcb = Ipakcb::fromInteger(0);
        $nrchop = Nrchop::highestActiveCell();
        $stressPeriodData = RchStressPeriodData::create()->addStressPeriodValue(RchStressPeriodValue::fromParams(0, Rech::fromFloat(0)));
        $irch = Irch::fromInteger(0);
        $extension = Extension::fromString('rch');
        $unitnumber = Unitnumber::fromInteger(19);

        return new self($ipakcb, $nrchop, $stressPeriodData, $irch, $extension, $unitnumber);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param Ipakcb $ipakcb
     * @param Nrchop $nrchop
     * @param RchStressPeriodData $stressPeriodData
     * @param Irch $irch
     * @param Extension $extension
     * @param Unitnumber $unitnumber
     * @return RchPackage
     */
    public static function fromParams(
        Ipakcb $ipakcb,
        Nrchop $nrchop,
        RchStressPeriodData $stressPeriodData,
        Irch $irch,
        Extension $extension,
        Unitnumber $unitnumber
    ): RchPackage
    {
        return new self($ipakcb, $nrchop, $stressPeriodData, $irch, $extension, $unitnumber);
    }

    public static function fromArray(array $arr): RchPackage
    {
        $ipakcb = Ipakcb::fromInteger($arr['ipakcb']);
        $nrchop = Nrchop::fromInteger($arr['nrchop']);
        $stressPeriodData = RchStressPeriodData::fromArray((array)$arr['stress_period_data']);
        $irch = Irch::fromValue($arr['irch']);
        $extension = Extension::fromValue($arr['extension']);
        $unitnumber = Unitnumber::fromValue($arr['unitnumber']);

        return new self($ipakcb, $nrchop, $stressPeriodData, $irch, $extension, $unitnumber);
    }

    private function __construct(
        Ipakcb $ipakcb,
        Nrchop $nrchop,
        RchStressPeriodData $stressPeriodData,
        Irch $irch,
        Extension $extension,
        Unitnumber $unitnumber
    )
    {
        $this->ipakcb = $ipakcb;
        $this->nrchop = $nrchop;
        $this->stressPeriodData = $stressPeriodData;
        $this->irch = $irch;
        $this->extension = $extension;
        $this->unitnumber = $unitnumber;
    }

    public function updateIpakcb(Ipakcb $ipakcb): RchPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ipakcb = $ipakcb;
        return $package;
    }

    public function updateNrchop(Nrchop $nrchop): RchPackage
    {
        $package = self::fromArray($this->toArray());
        $package->nrchop = $nrchop;
        return $package;
    }

    public function updateStressPeriodData(RchStressPeriodData $stressPeriodData): RchPackage
    {
        $package = self::fromArray($this->toArray());
        $package->stressPeriodData = $stressPeriodData;
        return $package;
    }

    public function updateIrch(Irch $irch): RchPackage
    {
        $package = self::fromArray($this->toArray());
        $package->irch = $irch;
        return $package;
    }

    public function updateExtension(Extension $extension): RchPackage
    {
        $package = self::fromArray($this->toArray());
        $package->extension = $extension;
        return $package;
    }

    public function updateUnitnumber(Unitnumber $unitnumber): RchPackage
    {
        $package = self::fromArray($this->toArray());
        $package->unitnumber = $unitnumber;
        return $package;
    }

    public function isValid(): bool
    {
        return $this->stressPeriodData->hasData();
    }

    public function toArray(): array
    {
        return array(
            'ipakcb' => $this->ipakcb->toInteger(),
            'nrchop' => $this->nrchop->toInteger(),
            'stress_period_data' => (object)$this->stressPeriodData->toArray(),
            'irch' => $this->irch->toValue(),
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
