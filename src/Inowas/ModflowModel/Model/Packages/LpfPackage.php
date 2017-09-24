<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

use Inowas\Common\Modflow\Chani;
use Inowas\Common\Modflow\Constantcv;
use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\Hani;
use Inowas\Common\Modflow\Hdry;
use Inowas\Common\Modflow\Hk;
use Inowas\Common\Modflow\Ihdwet;
use Inowas\Common\Modflow\Ipakcb;
use Inowas\Common\Modflow\Iwetit;
use Inowas\Common\Modflow\Layavg;
use Inowas\Common\Modflow\Laytyp;
use Inowas\Common\Modflow\Layvka;
use Inowas\Common\Modflow\Laywet;
use Inowas\Common\Modflow\Nocvcorrection;
use Inowas\Common\Modflow\Novfc;
use Inowas\Common\Modflow\Ss;
use Inowas\Common\Modflow\Storagecoefficient;
use Inowas\Common\Modflow\Sy;
use Inowas\Common\Modflow\Thickstrt;
use Inowas\Common\Modflow\Unitnumber;
use Inowas\Common\Modflow\Vka;
use Inowas\Common\Modflow\Vkcb;
use Inowas\Common\Modflow\Wetdry;
use Inowas\Common\Modflow\Wetfct;

class LpfPackage implements PackageInterface
{
    /** @var string  */
    protected $type = 'lpf';

    /** @var  Laytyp */
    protected $laytyp;

    /** @var  Layavg */
    protected $layavg;

    /** @var  Chani */
    protected $chani;

    /** @var  Layvka */
    protected $layvka;

    /** @var  Laywet */
    protected $laywet;

    /** @var  Ipakcb */
    protected $ipakcb;

    /** @var  Hdry */
    protected $hdry;

    /** @var  Wetfct */
    protected $wetfct;

    /** @var  Iwetit */
    protected $iwetit;

    /** @var  Ihdwet */
    protected $ihdwet;

    /** @var  Hk */
    protected $hk;

    /** @var  Hani */
    protected $hani;

    /** @var Vka */
    protected $vka;

    /** @var  Ss */
    protected $ss;

    /** @var  Sy */
    protected $sy;

    /** @var  Vkcb */
    protected $vkcb;

    /** @var  Wetdry */
    protected $wetdry;

    /** @var  Storagecoefficient */
    protected $storagecoefficient;

    /** @var  Constantcv */
    protected $constantcv;

    /** @var  Thickstrt */
    protected $thickstrt;

    /** @var  Nocvcorrection */
    protected $nocvcorrection;

    /** @var  Novfc */
    protected $novfc;

    /** @var  Extension */
    protected $extension;

    /** @var  Unitnumber */
    protected $unitnumber;

    /**
     * @return LpfPackage
     */
    public static function fromDefaults(): LpfPackage
    {
        $laytyp = Laytyp::fromInt(0);
        $layavg = Layavg::fromInt(0);
        $chani = Chani::fromFloat(1.0);
        $layvka = Layvka::fromFloat(0);
        $laywet = Laywet::fromFloat(0);
        $ipakcb = Ipakcb::fromInteger(53);
        $hdry = Hdry::fromFloat(-1E30);
        $wetfct = Wetfct::fromFloat(0.1);
        $iwetit = Iwetit::fromInteger(1);
        $ihdwet = Ihdwet::fromInteger(0);
        $hk = Hk::fromValue(1.0);
        $hani = Hani::fromValue(1.0);
        $vka = Vka::fromFloat(1.0);
        $ss = Ss::fromFloat(1e-5);
        $sy = Sy::fromFloat(0.15);
        $vkcb = Vkcb::fromFloat(0.0);
        $wetdry = Wetdry::fromFloat(-0.01);
        $storagecoefficient = Storagecoefficient::fromBool(false);
        $constantcv = Constantcv::fromBool(false);
        $thickstrt = Thickstrt::fromBool(false);
        $nocvcorrection = Nocvcorrection::fromBool(false);
        $novfc = Novfc::fromBool(false);
        $extension = Extension::fromString('lpf');
        $unitnumber = Unitnumber::fromInteger(15);

        return new self(
            $laytyp, $layavg, $chani, $layvka,
            $laywet, $ipakcb, $hdry, $wetfct,
            $iwetit, $ihdwet, $hk, $hani, $vka,
            $ss, $sy, $vkcb, $wetdry, $storagecoefficient,
            $constantcv, $thickstrt, $nocvcorrection,
            $novfc, $extension, $unitnumber
        );
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param Laytyp $laytyp
     * @param Layavg $layavg
     * @param Chani $chani
     * @param Layvka $layvka
     * @param Laywet $laywet
     * @param Ipakcb $ipakcb
     * @param Hdry $hdry
     * @param Wetfct $wetfct
     * @param Iwetit $iwetit
     * @param Ihdwet $ihdwet
     * @param Hk $hk
     * @param Hani $hani
     * @param Vka $vka
     * @param Ss $ss
     * @param Sy $sy
     * @param Vkcb $vkcb
     * @param Wetdry $wetdry
     * @param Storagecoefficient $storagecoefficient
     * @param Constantcv $constantcv
     * @param Thickstrt $thickstrt
     * @param Nocvcorrection $nocvcorrection
     * @param Novfc $novfc
     * @param Extension $extension
     * @param Unitnumber $unitnumber
     * @return LpfPackage
     */
    public static function fromParams(
        Laytyp $laytyp,
        Layavg $layavg,
        Chani $chani,
        Layvka $layvka,
        Laywet $laywet,
        Ipakcb $ipakcb,
        Hdry $hdry,
        Wetfct $wetfct,
        Iwetit $iwetit,
        Ihdwet $ihdwet,
        Hk $hk,
        Hani $hani,
        Vka $vka,
        Ss $ss,
        Sy $sy,
        Vkcb $vkcb,
        Wetdry $wetdry,
        Storagecoefficient $storagecoefficient,
        Constantcv $constantcv,
        Thickstrt $thickstrt,
        Nocvcorrection $nocvcorrection,
        Novfc $novfc,
        Extension $extension,
        Unitnumber $unitnumber
    ): LpfPackage
    {
        return new self(
            $laytyp, $layavg, $chani, $layvka,
            $laywet, $ipakcb, $hdry, $wetfct,
            $iwetit, $ihdwet, $hk, $hani, $vka,
            $ss, $sy, $vkcb, $wetdry, $storagecoefficient,
            $constantcv, $thickstrt, $nocvcorrection,
            $novfc, $extension, $unitnumber
        );
    }

    public static function fromArray(array $arr): LpfPackage
    {
        $laytyp = Laytyp::fromValue($arr['laytyp']);
        $layavg = Layavg::fromValue($arr['layavg']);
        $chani = Chani::fromValue($arr['chani']);
        $layvka = Layvka::fromValue($arr['layvka']);
        $laywet = Laywet::fromValue($arr['laywet']);
        $ipakcb = Ipakcb::fromValue($arr['ipakcb']);
        $hdry = Hdry::fromValue($arr['hdry']);
        $wetfct = Wetfct::fromValue($arr['wetfct']);
        $iwetit = Iwetit::fromValue($arr['iwetit']);
        $ihdwet = Ihdwet::fromValue($arr['ihdwet']);
        $hk = Hk::fromValue($arr['hk']);
        $hani = Hani::fromValue($arr['hani']);
        $vka = Vka::fromValue($arr['vka']);
        $ss = Ss::fromValue($arr['ss']);
        $sy = Sy::fromValue($arr['sy']);
        $vkcb = Vkcb::fromValue($arr['vkcb']);
        $wetdry = Wetdry::fromValue($arr['wetdry']);
        $storagecoefficient = Storagecoefficient::fromValue($arr['storagecoefficient']);
        $constantcv = Constantcv::fromValue($arr['constantcv']);
        $thickstrt = Thickstrt::fromValue($arr['thickstrt']);
        $nocvcorrection = Nocvcorrection::fromValue($arr['nocvcorrection']);
        $novfc = Novfc::fromValue($arr['novfc']);
        $extension = Extension::fromValue($arr['extension']);
        $unitnumber = Unitnumber::fromValue($arr['unitnumber']);

        return new self(
            $laytyp, $layavg, $chani, $layvka,
            $laywet, $ipakcb, $hdry, $wetfct,
            $iwetit, $ihdwet, $hk, $hani, $vka,
            $ss, $sy, $vkcb, $wetdry, $storagecoefficient,
            $constantcv, $thickstrt, $nocvcorrection,
            $novfc, $extension, $unitnumber
        );
    }

    public function type(): string
    {
        return $this->type;
    }

    public function updateLaytyp(Laytyp $laytyp): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->laytyp = $laytyp;
        return $package;
    }

    public function updateLayavg(Layavg $layavg): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->layavg = $layavg;
        return $package;
    }

    public function updateChani(Chani $chani): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->chani = $chani;
        return $package;
    }

    public function updateLayvka(Layvka $layvka): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->layvka = $layvka;
        return $package;
    }

    public function updateLaywet(Laywet $laywet): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->laywet = $laywet;
        return $package;
    }

    public function updateIpakcb(Ipakcb $ipakcb): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ipakcb = $ipakcb;
        return $package;
    }

    public function updateHdry(Hdry $hdry): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->hdry = $hdry;
        return $package;
    }

    public function updateWetfct(Wetfct $wetfct): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->wetfct = $wetfct;
        return $package;
    }

    public function updateIwetit(Iwetit $iwetit): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->iwetit = $iwetit;
        return $package;
    }

    public function updateIhdwet(Ihdwet $ihdwet): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ihdwet = $ihdwet;
        return $package;
    }

    public function updateHk(Hk $hk): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->hk = $hk;
        return $package;
    }

    public function updateHani(Hani $hani): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->hani = $hani;
        return $package;
    }

    public function updateVka(Vka $vka): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->vka = $vka;
        return $package;
    }

    public function updateSs(Ss $ss): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ss = $ss;
        return $package;
    }

    public function updateSy(Sy $sy): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->sy = $sy;
        return $package;
    }

    public function updateVkcb(Vkcb $vkcb): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->vkcb = $vkcb;
        return $package;
    }

    public function updateWetdry(Wetdry $wetdry): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->wetdry = $wetdry;
        return $package;
    }

    public function updateStoragecoefficient(Storagecoefficient $storagecoefficient): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->storagecoefficient = $storagecoefficient;
        return $package;
    }

    public function updateConstantcv(Constantcv $constantcv): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->constantcv = $constantcv;
        return $package;
    }

    public function updateThickstrt(Thickstrt $thickstrt): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->thickstrt = $thickstrt;
        return $package;
    }

    public function updateNocvcorrection(Nocvcorrection $nocvcorrection): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->nocvcorrection = $nocvcorrection;
        return $package;
    }

    public function updateNovfc(Novfc $novfc): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->novfc = $novfc;
        return $package;
    }

    public function updateExtension(Extension $extension): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->extension = $extension;
        return $package;
    }

    public function updateUnitnumber(Unitnumber $unitnumber): LpfPackage
    {
        $package = self::fromArray($this->toArray());
        $package->unitnumber = $unitnumber;
        return $package;
    }

    private function __construct(
        Laytyp $layTyp,
        Layavg $layAvg,
        Chani $chani,
        Layvka $layVka,
        Laywet $layWet,
        Ipakcb $ipakcb,
        Hdry $hdry,
        Wetfct $wetFct,
        Iwetit $iWetIt,
        Ihdwet $ihdWet,
        Hk $hk,
        Hani $hani,
        Vka $vka,
        Ss $ss,
        Sy $sy,
        Vkcb $vkcb,
        Wetdry $wetDry,
        Storagecoefficient $storageCoefficient,
        Constantcv $constantCv,
        Thickstrt $thickStrt,
        Nocvcorrection $noCvCorrection,
        Novfc $noVfc,
        Extension $extension,
        Unitnumber $unitNumber
    )
    {
        $this->laytyp = $layTyp;
        $this->layavg = $layAvg;
        $this->chani = $chani;
        $this->layvka = $layVka;
        $this->laywet = $layWet;
        $this->ipakcb = $ipakcb;
        $this->hdry = $hdry;
        $this->wetfct = $wetFct;
        $this->iwetit = $iWetIt;
        $this->ihdwet = $ihdWet;
        $this->hk = $hk;
        $this->hani = $hani;
        $this->vka = $vka;
        $this->ss = $ss;
        $this->sy = $sy;
        $this->vkcb = $vkcb;
        $this->wetdry = $wetDry;
        $this->storagecoefficient = $storageCoefficient;
        $this->constantcv = $constantCv;
        $this->thickstrt = $thickStrt;
        $this->nocvcorrection = $noCvCorrection;
        $this->novfc = $noVfc;
        $this->extension = $extension;
        $this->unitnumber = $unitNumber;
    }

    public function isValid(): bool
    {
        return true;
    }

    public function toArray(): array
    {
        return array(
            'laytyp' => $this->laytyp->toValue(),
            'layavg' => $this->layavg->toValue(),
            'chani' => $this->chani->toValue(),
            'layvka' => $this->layvka->toValue(),
            'laywet' => $this->laywet->toValue(),
            'ipakcb' => $this->ipakcb->toValue(),
            'hdry' => $this->hdry->toValue(),
            'wetfct' => $this->wetfct->toValue(),
            'iwetit' => $this->iwetit->toValue(),
            'ihdwet' => $this->ihdwet->toValue(),
            'hk' => $this->hk->toValue(),
            'hani' => $this->hani->toValue(),
            'vka' => $this->vka->toValue(),
            'ss' => $this->ss->toValue(),
            'sy' => $this->sy->toValue(),
            'vkcb' => $this->vkcb->toValue(),
            'wetdry' => $this->wetdry->toValue(),
            'storagecoefficient' => $this->storagecoefficient->toValue(),
            'constantcv' => $this->constantcv->toValue(),
            'thickstrt' => $this->thickstrt->toValue(),
            'nocvcorrection' => $this->nocvcorrection->toValue(),
            'novfc' => $this->novfc->toValue(),
            'extension' => $this->extension->toValue(),
            'unitnumber' => $this->unitnumber->toValue()
        );
    }

    public function getEditables(): array
    {
        return array(
            'hdry' => $this->hdry->toValue(),
            'wetfct' => $this->wetfct->toValue(),
            'iwetit' => $this->iwetit->toValue(),
            'ihdwet' => $this->ihdwet->toValue(),
            'vkcb' => $this->vkcb->toValue(),
            'wetdry' => $this->wetdry->toValue(),
            'storagecoefficient' => $this->storagecoefficient->toValue(),
            'constantcv' => $this->constantcv->toValue(),
            'nocvcorrection' => $this->nocvcorrection->toValue(),
            'novfc' => $this->novfc->toValue()
        );
    }

    public function mergeEditables(array $arr): void
    {
        $this->hdry = Hdry::fromValue($arr['hdry']);
        $this->wetfct = Wetfct::fromValue($arr['wetfct']);
        $this->iwetit = Iwetit::fromValue($arr['iwetit']);
        $this->ihdwet = Ihdwet::fromValue($arr['ihdwet']);
        $this->vkcb = Vkcb::fromValue($arr['vkcb']);
        $this->wetdry = Wetdry::fromValue($arr['wetdry']);
        $this->storagecoefficient = Storagecoefficient::fromValue($arr['storagecoefficient']);
        $this->constantcv = Constantcv::fromValue($arr['constantcv']);
        $this->nocvcorrection = Nocvcorrection::fromValue($arr['nocvcorrection']);
        $this->novfc = Novfc::fromValue($arr['novfc']);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
