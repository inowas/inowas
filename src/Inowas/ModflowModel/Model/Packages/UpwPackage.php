<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

use Inowas\Common\Modflow\Chani;
use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\Hani;
use Inowas\Common\Modflow\Hdry;
use Inowas\Common\Modflow\Hk;
use Inowas\Common\Modflow\Ipakcb;
use Inowas\Common\Modflow\Iphdry;
use Inowas\Common\Modflow\Layavg;
use Inowas\Common\Modflow\Laytyp;
use Inowas\Common\Modflow\Layvka;
use Inowas\Common\Modflow\Laywet;
use Inowas\Common\Modflow\Ss;
use Inowas\Common\Modflow\Sy;
use Inowas\Common\Modflow\Unitnumber;
use Inowas\Common\Modflow\Vka;
use Inowas\Common\Modflow\Vkcb;

class UpwPackage extends AbstractPackage
{
    const TYPE = 'upw';
    const DESCRIPTION = 'Upstream Weighting Package';

    /** @var string  */
    protected $type = 'upw';

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

    /** @var  Iphdry */
    protected $iphdry;

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

    /** @var  Extension */
    protected $extension;

    /** @var  Unitnumber */
    protected $unitnumber;

    /**
     * @return UpwPackage
     */
    public static function fromDefaults(): UpwPackage
    {
        $laytyp = Laytyp::fromInt(0);
        $layavg = Layavg::fromInt(0);
        $chani = Chani::fromFloat(1.0);
        $layvka = Layvka::fromFloat(0);
        $laywet = Laywet::fromFloat(0);
        $ipakcb = Ipakcb::fromInteger(53);
        $hdry = Hdry::fromFloat(-1E30);
        $iphdry = Iphdry::fromInt(0);
        $hk = Hk::fromValue(1.0);
        $hani = Hani::fromValue(1.0);
        $vka = Vka::fromFloat(1.0);
        $ss = Ss::fromFloat(1e-5);
        $sy = Sy::fromFloat(0.15);
        $vkcb = Vkcb::fromFloat(0.0);
        $extension = Extension::fromString('upw');
        $unitnumber = Unitnumber::fromInteger(31);

        return new self(
            $laytyp, $layavg, $chani, $layvka,
            $laywet, $ipakcb, $hdry, $iphdry,
            $hk, $hani, $vka, $ss, $sy, $vkcb,
            $extension, $unitnumber
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
     * @param Iphdry $iphdry
     * @param Hk $hk
     * @param Hani $hani
     * @param Vka $vka
     * @param Ss $ss
     * @param Sy $sy
     * @param Vkcb $vkcb
     * @param Extension $extension
     * @param Unitnumber $unitnumber
     * @return UpwPackage
     */
    public static function fromParams(
        Laytyp $laytyp,
        Layavg $layavg,
        Chani $chani,
        Layvka $layvka,
        Laywet $laywet,
        Ipakcb $ipakcb,
        Hdry $hdry,
        Iphdry $iphdry,
        Hk $hk,
        Hani $hani,
        Vka $vka,
        Ss $ss,
        Sy $sy,
        Vkcb $vkcb,
        Extension $extension,
        Unitnumber $unitnumber
    ): UpwPackage
    {
        return new self(
            $laytyp, $layavg, $chani, $layvka,
            $laywet, $ipakcb, $hdry, $iphdry,
            $hk, $hani, $vka, $ss, $sy, $vkcb,
            $extension, $unitnumber
        );
    }

    public static function fromArray(array $arr): UpwPackage
    {
        $laytyp = Laytyp::fromValue($arr['laytyp']);
        $layavg = Layavg::fromValue($arr['layavg']);
        $chani = Chani::fromValue($arr['chani']);
        $layvka = Layvka::fromValue($arr['layvka']);
        $laywet = Laywet::fromValue($arr['laywet']);
        $ipakcb = Ipakcb::fromValue($arr['ipakcb']);
        $hdry = Hdry::fromValue($arr['hdry']);
        $iphdry = Iphdry::fromValue($arr['iphdry']);
        $hk = Hk::fromValue($arr['hk']);
        $hani = Hani::fromValue($arr['hani']);
        $vka = Vka::fromValue($arr['vka']);
        $ss = Ss::fromValue($arr['ss']);
        $sy = Sy::fromValue($arr['sy']);
        $vkcb = Vkcb::fromValue($arr['vkcb']);
        $extension = Extension::fromValue($arr['extension']);
        $unitnumber = Unitnumber::fromValue($arr['unitnumber']);

        return new self(
            $laytyp, $layavg, $chani, $layvka,
            $laywet, $ipakcb, $hdry, $iphdry,
            $hk, $hani, $vka, $ss, $sy, $vkcb,
            $extension, $unitnumber
        );
    }

    public function updateLaytyp(Laytyp $laytyp): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->laytyp = $laytyp;
        return $package;
    }

    public function updateLayavg(Layavg $layavg): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->layavg = $layavg;
        return $package;
    }

    public function updateChani(Chani $chani): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->chani = $chani;
        return $package;
    }

    public function updateLayvka(Layvka $layvka): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->layvka = $layvka;
        return $package;
    }

    public function updateLaywet(Laywet $laywet): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->laywet = $laywet;
        return $package;
    }

    public function updateIpakcb(Ipakcb $ipakcb): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ipakcb = $ipakcb;
        return $package;
    }

    public function updateHdry(Hdry $hdry): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->hdry = $hdry;
        return $package;
    }

    public function updateIphdry(Iphdry $iphdry): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->iphdry = $iphdry;
        return $package;
    }

    public function updateHk(Hk $hk): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->hk = $hk;
        return $package;
    }

    public function updateHani(Hani $hani): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->hani = $hani;
        return $package;
    }

    public function updateVka(Vka $vka): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->vka = $vka;
        return $package;
    }

    public function updateSs(Ss $ss): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ss = $ss;
        return $package;
    }

    public function updateSy(Sy $sy): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->sy = $sy;
        return $package;
    }

    public function updateVkcb(Vkcb $vkcb): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->vkcb = $vkcb;
        return $package;
    }

    public function updateExtension(Extension $extension): UpwPackage
    {
        $package = self::fromArray($this->toArray());
        $package->extension = $extension;
        return $package;
    }

    public function updateUnitnumber(Unitnumber $unitnumber): UpwPackage
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
        Iphdry $iphdry,
        Hk $hk,
        Hani $hani,
        Vka $vka,
        Ss $ss,
        Sy $sy,
        Vkcb $vkcb,
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
        $this->iphdry = $iphdry;
        $this->hk = $hk;
        $this->hani = $hani;
        $this->vka = $vka;
        $this->ss = $ss;
        $this->sy = $sy;
        $this->vkcb = $vkcb;
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
            'iphdry' => $this->iphdry->toValue(),
            'hk' => $this->hk->toValue(),
            'hani' => $this->hani->toValue(),
            'vka' => $this->vka->toValue(),
            'ss' => $this->ss->toValue(),
            'sy' => $this->sy->toValue(),
            'vkcb' => $this->vkcb->toValue(),
            'extension' => $this->extension->toValue(),
            'unitnumber' => $this->unitnumber->toValue()
        );
    }

    public function getEditables(): array
    {
        return array(
            'hdry' => $this->hdry->toValue(),
            'iphdry' => $this->iphdry->toValue(),
            'vkcb' => $this->vkcb->toValue()
        );
    }

    public function mergeEditables(array $arr): void
    {
        $this->hdry = Hdry::fromValue($arr['hdry']);
        $this->iphdry = Iphdry::fromValue($arr['iphdry']);
        $this->vkcb = Vkcb::fromValue($arr['vkcb']);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
