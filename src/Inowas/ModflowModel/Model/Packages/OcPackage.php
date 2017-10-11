<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

use Inowas\Common\Modflow\Cboufm;
use Inowas\Common\Modflow\Cddnfm;
use Inowas\Common\Modflow\Chedfm;
use Inowas\Common\Modflow\Compact;
use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\Iddnfm;
use Inowas\Common\Modflow\Ihedfm;
use Inowas\Common\Modflow\Unitnumber;

class OcPackage extends AbstractPackage
{
    const TYPE = 'oc';
    const DESCRIPTION = 'Output-Control Package';

    /** @var  Ihedfm */
    protected $ihedfm;

    /** @var  Iddnfm */
    protected $iddnfm;

    /** @var  Chedfm */
    protected $chedfm;

    /** @var  Cddnfm */
    protected $cddnfm;

    /** @var  Cboufm */
    protected $cboufm;

    /** @var  OcStressPeriodData */
    protected $stressPeriodData;

    /** @var  Compact */
    protected $compact;

    /** @var Extension */
    protected $extension;

    /** @var  Unitnumber */
    protected $unitnumber;


    public static function fromDefaults(): OcPackage
    {
        $ihedfm = Ihedfm::fromInteger(0);
        $iddnfm = Iddnfm::fromInteger(0);
        $chedfm = Chedfm::fromValue(null);
        $cddnfm = Cddnfm::fromValue(null);
        $cboufm = Cboufm::fromValue(null);
        $stressPeriodData = OcStressPeriodData::create()->addStressPeriod(OcStressPeriod::fromParams(0,0, ['save head']));
        $extension = Extension::fromArray(['oc', 'hds', 'ddn', 'cbc']);
        $unitnumber = Unitnumber::fromArray([14, 51, 52, 53]);

        return new self($ihedfm, $iddnfm, $chedfm, $cddnfm, $cboufm, $stressPeriodData, $extension, $unitnumber);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param Ihedfm $ihedfm
     * @param Iddnfm $iddnfm
     * @param Chedfm $chedfm
     * @param Cddnfm $cddnfm
     * @param Cboufm $cboufm
     * @param OcStressPeriodData $stressPeriodData
     * @param Extension $extension
     * @param Unitnumber $unitnumber
     * @return OcPackage
     */
    public static function fromParams(
        Ihedfm $ihedfm,
        Iddnfm $iddnfm,
        Chedfm $chedfm,
        Cddnfm $cddnfm,
        Cboufm $cboufm,
        OcStressPeriodData $stressPeriodData,
        Extension $extension,
        Unitnumber $unitnumber
    ): OcPackage
    {
        return new self($ihedfm, $iddnfm, $chedfm, $cddnfm, $cboufm, $stressPeriodData, $extension, $unitnumber);
    }

    public static function fromArray(array $arr): OcPackage
    {
        $ihedfm = Ihedfm::fromInteger($arr['ihedfm']);
        $iddnfm = Iddnfm::fromInteger($arr['iddnfm']);
        $chedfm = Chedfm::fromValue($arr['chedfm']);
        $cddnfm = Cddnfm::fromValue($arr['cddnfm']);
        $cboufm = Cboufm::fromValue($arr['cboufm']);
        $stressPeriodData = OcStressPeriodData::fromArray($arr['stress_period_data']);
        $extension = Extension::fromArray($arr['extension']);
        $unitnumber = Unitnumber::fromArray($arr['unitnumber']);

        return new self($ihedfm, $iddnfm, $chedfm, $cddnfm, $cboufm, $stressPeriodData, $extension, $unitnumber);
    }

    private function __construct(
        Ihedfm $ihedfm,
        Iddnfm $iddnfm,
        Chedfm $chedfm,
        Cddnfm $cddnfm,
        Cboufm $cboufm,
        OcStressPeriodData $stressPeriodData,
        Extension $extension,
        Unitnumber $unitnumber
    )
    {
        $this->ihedfm = $ihedfm;
        $this->iddnfm = $iddnfm;
        $this->chedfm = $chedfm;
        $this->cddnfm = $cddnfm;
        $this->cboufm = $cboufm;
        $this->stressPeriodData = $stressPeriodData;
        $this->extension = $extension;
        $this->unitnumber = $unitnumber;
    }

    public function updateIhedfm(Ihedfm $ihedfm): OcPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ihedfm = $ihedfm;
        return $package;
    }

    public function updateIddnfm(Iddnfm $iddnfm): OcPackage
    {
        $package = self::fromArray($this->toArray());
        $package->iddnfm = $iddnfm;
        return $package;
    }

    public function updateChedfm(Chedfm $chedfm): OcPackage
    {
        $package = self::fromArray($this->toArray());
        $package->chedfm = $chedfm;
        return $package;
    }

    public function updateCddnfm(Cddnfm $cddnfm): OcPackage
    {
        $package = self::fromArray($this->toArray());
        $package->cddnfm = $cddnfm;
        return $package;
    }

    public function updateCboufm(Cboufm $cboufm): OcPackage
    {
        $package = self::fromArray($this->toArray());
        $package->cboufm = $cboufm;
        return $package;
    }

    public function updateOcStressPeriodData(OcStressPeriodData $ocStressPeriodData): OcPackage
    {
        $package = self::fromArray($this->toArray());
        $package->stressPeriodData = $ocStressPeriodData;
        return $package;
    }

    public function updateCompact(Compact $compact): OcPackage
    {
        $package = self::fromArray($this->toArray());
        $package->compact = $compact;
        return $package;
    }

    public function updateExtension(Extension $extension): OcPackage
    {
        $package = self::fromArray($this->toArray());
        $package->extension = $extension;
        return $package;
    }

    public function updateUnitnumber(Unitnumber $unitnumber): OcPackage
    {
        $package = self::fromArray($this->toArray());
        $package->unitnumber = $unitnumber;
        return $package;
    }

    public function isValid(): bool
    {
        return true;
    }

    public function toArray(): array
    {
        return array(
            'ihedfm' => $this->ihedfm->toInteger(),
            'iddnfm' => $this->iddnfm->toInteger(),
            'chedfm' => $this->chedfm->toValue(),
            'cddnfm' => $this->cddnfm->toValue(),
            'cboufm' => $this->cboufm->toValue(),
            'stress_period_data' => $this->stressPeriodData->toArray(),
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
