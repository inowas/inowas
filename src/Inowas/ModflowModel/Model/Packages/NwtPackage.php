<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

use Inowas\Common\Modflow\Backflag;
use Inowas\Common\Modflow\Backreduce;
use Inowas\Common\Modflow\Backtol;
use Inowas\Common\Modflow\Dbdgamma;
use Inowas\Common\Modflow\Dbdkappa;
use Inowas\Common\Modflow\Dbdtheta;
use Inowas\Common\Modflow\Epsrn;
use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\Fluxtol;
use Inowas\Common\Modflow\Hclosexmd;
use Inowas\Common\Modflow\Headtol;
use Inowas\Common\Modflow\Iacl;
use Inowas\Common\Modflow\Ibotavg;
use Inowas\Common\Modflow\Idroptol;
use Inowas\Common\Modflow\Ilumethod;
use Inowas\Common\Modflow\Iprnwt;
use Inowas\Common\Modflow\Iredsys;
use Inowas\Common\Modflow\Level;
use Inowas\Common\Modflow\Levfill;
use Inowas\Common\Modflow\Linmeth;
use Inowas\Common\Modflow\Maxbackiter;
use Inowas\Common\Modflow\Maxinner;
use Inowas\Common\Modflow\Maxiterout;
use Inowas\Common\Modflow\Momfact;
use Inowas\Common\Modflow\Msdr;
use Inowas\Common\Modflow\Mxiterxmd;
use Inowas\Common\Modflow\Norder;
use Inowas\Common\Modflow\North;
use Inowas\Common\Modflow\NwtContinue;
use Inowas\Common\Modflow\NwtOptions;
use Inowas\Common\Modflow\Rrctools;
use Inowas\Common\Modflow\Stoptol;
use Inowas\Common\Modflow\Thickfact;
use Inowas\Common\Modflow\Unitnumber;

class NwtPackage implements PackageInterface
{
    /** @var string  */
    protected $type = 'nwt';

    /** @var Headtol */
    protected $headtol;

    /** @var Fluxtol */
    protected $fluxtol;

    /** @var Maxiterout */
    protected $maxiterout;

    /** @var Thickfact */
    protected $thickfact;

    /** @var Linmeth */
    protected $linmeth;

    /** @var Iprnwt */
    protected $iprnwt;

    /** @var Ibotavg */
    protected $ibotavg;

    /** @var NwtOptions */
    protected $options;

    /** @var NwtContinue */
    protected $continue;

    /** @var Dbdtheta */
    protected $dbdtheta;

    /** @var Dbdkappa */
    protected $dbdkappa;

    /** @var Dbdgamma */
    protected $dbdgamma;

    /** @var Momfact */
    protected $momfact;

    /** @var Backflag */
    protected $backflag;

    /** @var Maxbackiter */
    protected $maxbackiter;

    /** @var Backtol */
    protected $backtol;

    /** @var Backreduce */
    protected $backreduce;

    /** @var Maxinner */
    protected $maxitinner;

    /** @var Ilumethod */
    protected $ilumethod;

    /** @var Levfill */
    protected $levfill;

    /** @var Stoptol */
    protected $stoptol;

    /** @var Msdr */
    protected $msdr;

    /** @var Iacl */
    protected $iacl;

    /** @var Norder */
    protected $norder;

    /** @var Level */
    protected $level;

    /** @var North */
    protected $north;

    /** @var Iredsys */
    protected $iredsys;

    /** @var Rrctools */
    protected $rrctols;

    /** @var Idroptol */
    protected $idroptol;

    /** @var Epsrn */
    protected $epsrn;

    /** @var Hclosexmd */
    protected $hclosexmd;

    /** @var Mxiterxmd */
    protected $mxiterxmd;

    /** @var  Extension */
    protected $extension;

    /** @var  Unitnumber */
    protected $unitnumber;

    /**
     * @return NwtPackage
     */
    public static function fromDefaults(): NwtPackage
    {
        $headtol = Headtol::fromFloat(1E-2);
        $fluxtol = Fluxtol::fromFloat(500);
        $maxiterout = Maxiterout::fromInteger(100);
        $thickfact = Thickfact::fromFloat(1E-5);
        $linmeth = Linmeth::fromInteger(1);
        $iprnwt = Iprnwt::fromInteger(0);
        $ibotavg = Ibotavg::fromInteger(0);
        $options = NwtOptions::fromString('COMPLEX');
        $continue = NwtContinue::fromBool(false);
        $dbdtheta = Dbdtheta::fromFloat(0.4);
        $dbdkappa = Dbdkappa::fromFloat(1E-5);
        $dbdgamma = Dbdgamma::fromFloat(0);
        $momfact = Momfact::fromFloat(0.1);
        $backflag = Backflag::fromInteger(1);
        $maxbackiter = Maxbackiter::fromInteger(50);
        $backtol = Backtol::fromFloat(1.1);
        $backreduce = Backreduce::fromFloat(0.7);
        $maxitinner = Maxinner::fromInteger(50);
        $ilumethod = Ilumethod::fromInteger(2);
        $levfill = Levfill::fromInteger(5);
        $stoptol = Stoptol::fromFloat(1.0e-10);
        $msdr = Msdr::fromInteger(15);
        $iacl = Iacl::fromInteger(2);
        $norder = Norder::fromInteger(1);
        $level = Level::fromInteger(5);
        $north = North::fromInteger(7);
        $iredsys = Iredsys::fromInteger(0);
        $rrctols = Rrctools::fromFloat(0.0);
        $idroptol = Idroptol::fromInteger(1);
        $epsrn = Epsrn::fromFloat(1.0E-4);
        $hclosexmd = Hclosexmd::fromFloat(1E-4);
        $mxiterxmd = Mxiterxmd::fromInteger(50);
        $extension = Extension::fromString('nwt');
        $unitnumber = Unitnumber::fromInteger(32);

        return new self(
            $headtol, $fluxtol, $maxiterout, $thickfact, $linmeth, $iprnwt, $ibotavg, $options, $continue, $dbdtheta,
            $dbdkappa, $dbdgamma, $momfact, $backflag, $maxbackiter, $backtol, $backreduce, $maxitinner, $ilumethod,
            $levfill, $stoptol, $msdr, $iacl, $norder, $level, $north, $iredsys, $rrctols, $idroptol, $epsrn,
            $hclosexmd, $mxiterxmd, $extension, $unitnumber
        );
    }


    /** @noinspection MoreThanThreeArgumentsInspection
     * @param Headtol $headtol
     * @param Fluxtol $fluxtol
     * @param Maxiterout $maxiterout
     * @param Thickfact $thickfact
     * @param Linmeth $linmeth
     * @param Iprnwt $iprnwt
     * @param Ibotavg $ibotavg
     * @param NwtOptions $options
     * @param NwtContinue $continue
     * @param Dbdtheta $dbdtheta
     * @param Dbdkappa $dbdkappa
     * @param Dbdgamma $dbdgamma
     * @param Momfact $momfact
     * @param Backflag $backflag
     * @param Maxbackiter $maxbackiter
     * @param Backtol $backtol
     * @param Backreduce $backreduce
     * @param Maxinner $maxitinner
     * @param Ilumethod $ilumethod
     * @param Levfill $levfill
     * @param Stoptol $stoptol
     * @param Msdr $msdr
     * @param Iacl $iacl
     * @param Norder $norder
     * @param Level $level
     * @param North $north
     * @param Iredsys $iredsys
     * @param Rrctools $rrctols
     * @param Idroptol $idroptol
     * @param Epsrn $epsrn
     * @param Hclosexmd $hclosexmd
     * @param Mxiterxmd $mxiterxmd
     * @param Extension $extension
     * @param Unitnumber $unitnumber
     * @return NwtPackage
     */
    public static function fromParams(
        Headtol $headtol, Fluxtol $fluxtol, Maxiterout $maxiterout, Thickfact $thickfact, Linmeth $linmeth,
        Iprnwt $iprnwt, Ibotavg $ibotavg, NwtOptions $options, NwtContinue $continue, Dbdtheta $dbdtheta,
        Dbdkappa $dbdkappa, Dbdgamma $dbdgamma, Momfact $momfact, Backflag $backflag, Maxbackiter $maxbackiter,
        Backtol $backtol, Backreduce $backreduce, Maxinner $maxitinner, Ilumethod $ilumethod, Levfill $levfill,
        Stoptol $stoptol, Msdr $msdr, Iacl $iacl, Norder $norder, Level $level, North $north, Iredsys $iredsys,
        Rrctools $rrctols, Idroptol $idroptol, Epsrn $epsrn, Hclosexmd $hclosexmd, Mxiterxmd $mxiterxmd,
        Extension $extension, Unitnumber $unitnumber
    ): NwtPackage
    {
        return new self(
            $headtol, $fluxtol, $maxiterout, $thickfact, $linmeth, $iprnwt, $ibotavg, $options, $continue, $dbdtheta,
            $dbdkappa, $dbdgamma, $momfact, $backflag, $maxbackiter, $backtol, $backreduce, $maxitinner, $ilumethod,
            $levfill, $stoptol, $msdr, $iacl, $norder, $level, $north, $iredsys, $rrctols, $idroptol, $epsrn,
            $hclosexmd, $mxiterxmd, $extension, $unitnumber
        );
    }

    private function __construct(
        Headtol $headtol, Fluxtol $fluxtol, Maxiterout $maxiterout, Thickfact $thickfact, Linmeth $linmeth,
        Iprnwt $iprnwt, Ibotavg $ibotavg, NwtOptions $options, NwtContinue $continue, Dbdtheta $dbdtheta,
        Dbdkappa $dbdkappa, Dbdgamma $dbdgamma, Momfact $momfact, Backflag $backflag, Maxbackiter $maxbackiter,
        Backtol $backtol, Backreduce $backreduce, Maxinner $maxitinner, Ilumethod $ilumethod, Levfill $levfill,
        Stoptol $stoptol, Msdr $msdr, Iacl $iacl, Norder $norder, Level $level, North $north, Iredsys $iredsys,
        Rrctools $rrctols, Idroptol $idroptol, Epsrn $epsrn, Hclosexmd $hclosexmd, Mxiterxmd $mxiterxmd,
        Extension $extension, Unitnumber $unitnumber
    )
    {
        $this->headtol = $headtol;
        $this->fluxtol = $fluxtol;
        $this->maxiterout = $maxiterout;
        $this->thickfact = $thickfact;
        $this->linmeth = $linmeth;
        $this->iprnwt = $iprnwt;
        $this->ibotavg = $ibotavg;
        $this->options = $options;
        $this->continue = $continue;
        $this->dbdtheta = $dbdtheta;
        $this->dbdkappa = $dbdkappa;
        $this->dbdgamma = $dbdgamma;
        $this->momfact = $momfact;
        $this->backflag = $backflag;
        $this->maxbackiter = $maxbackiter;
        $this->backtol = $backtol;
        $this->backreduce = $backreduce;
        $this->maxitinner = $maxitinner;
        $this->ilumethod = $ilumethod;
        $this->levfill = $levfill;
        $this->stoptol = $stoptol;
        $this->msdr = $msdr;
        $this->iacl = $iacl;
        $this->norder = $norder;
        $this->level = $level;
        $this->north = $north;
        $this->iredsys = $iredsys;
        $this->rrctols = $rrctols;
        $this->idroptol = $idroptol;
        $this->epsrn = $epsrn;
        $this->hclosexmd = $hclosexmd;
        $this->mxiterxmd = $mxiterxmd;
        $this->extension = $extension;
        $this->unitnumber = $unitnumber;
    }

    public static function fromArray(array $arr): NwtPackage
    {
        $headtol = Headtol::fromFloat($arr['headtol']);
        $fluxtol = Fluxtol::fromFloat($arr['fluxtol']);
        $maxiterout = Maxiterout::fromInteger($arr['maxiterout']);
        $thickfact = Thickfact::fromFloat($arr['thickfact']);
        $linmeth = Linmeth::fromInteger($arr['linmeth']);
        $iprnwt = Iprnwt::fromInteger($arr['iprnwt']);
        $ibotavg = Ibotavg::fromInteger($arr['ibotavg']);
        $options = NwtOptions::fromString($arr['options']);
        $continue = NwtContinue::fromBool($arr['continue']);
        $dbdtheta = Dbdtheta::fromFloat($arr['dbdtheta']);
        $dbdkappa = Dbdkappa::fromFloat($arr['dbdkappa']);
        $dbdgamma = Dbdgamma::fromFloat($arr['dbdgamma']);
        $momfact = Momfact::fromFloat($arr['momfact']);
        $backflag = Backflag::fromInteger($arr['backflag']);
        $maxbackiter = Maxbackiter::fromInteger($arr['maxbackiter']);
        $backtol = Backtol::fromFloat($arr['backtol']);
        $backreduce = Backreduce::fromFloat($arr['backreduce']);
        $maxitinner = Maxinner::fromInteger($arr['maxitinner']);
        $ilumethod = Ilumethod::fromInteger($arr['ilumethod']);
        $levfill = Levfill::fromInteger($arr['levfill']);
        $stoptol = Stoptol::fromFloat($arr['stoptol']);
        $msdr = Msdr::fromInteger($arr['msdr']);
        $iacl = Iacl::fromInteger($arr['iacl']);
        $norder = Norder::fromInteger($arr['norder']);
        $level = Level::fromInteger($arr['level']);
        $north = North::fromInteger($arr['north']);
        $iredsys = Iredsys::fromInteger($arr['iredsys']);
        $rrctols = Rrctools::fromFloat($arr['rrctols']);
        $idroptol = Idroptol::fromInteger($arr['idroptol']);
        $epsrn = Epsrn::fromFloat($arr['epsrn']);
        $hclosexmd = Hclosexmd::fromFloat($arr['hclosexmd']);
        $mxiterxmd = Mxiterxmd::fromInteger($arr['mxiterxmd']);
        $extension = Extension::fromString($arr['extension']);
        $unitnumber = Unitnumber::fromInteger($arr['unitnumber']);

        return new self(
            $headtol, $fluxtol, $maxiterout, $thickfact, $linmeth, $iprnwt, $ibotavg, $options, $continue, $dbdtheta,
            $dbdkappa, $dbdgamma, $momfact, $backflag, $maxbackiter, $backtol, $backreduce, $maxitinner, $ilumethod,
            $levfill, $stoptol, $msdr, $iacl, $norder, $level, $north, $iredsys, $rrctols, $idroptol, $epsrn,
            $hclosexmd, $mxiterxmd, $extension, $unitnumber
        );
    }

    public function type(): string
    {
        return $this->type;
    }

    public function updateHeadtol(Headtol $headtol): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->headtol = $headtol;
        return $package;
    }

    public function updateFluxtol(Fluxtol $fluxtol): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->fluxtol = $fluxtol;
        return $package;
    }

    public function updateMaxiterout(Maxiterout $maxiterout): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->maxiterout = $maxiterout;
        return $package;
    }

    public function updateThickfact(Thickfact $thickfact): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->thickfact = $thickfact;
        return $package;
    }

    public function updateLinmeth(Linmeth $linmeth): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->linmeth = $linmeth;
        return $package;
    }

    public function updateIprnwt(Iprnwt $iprnwt): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->iprnwt = $iprnwt;
        return $package;
    }

    public function updateIbotavg(Ibotavg $ibotavg): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ibotavg = $ibotavg;
        return $package;
    }

    public function updateNwtOptions(NwtOptions $options): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->options = $options;
        return $package;
    }

    public function updateNwtContinue(NwtContinue $continue): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->continue = $continue;
        return $package;
    }

    public function updateDbdtheta(Dbdtheta $dbdtheta): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->dbdtheta = $dbdtheta;
        return $package;
    }

    public function updateDbdkappa(Dbdkappa $dbdkappa): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->dbdkappa = $dbdkappa;
        return $package;
    }

    public function updateDbdgamma(Dbdgamma $dbdgamma): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->dbdgamma = $dbdgamma;
        return $package;
    }

    public function updateMomfact(Momfact $momfact): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->momfact = $momfact;
        return $package;
    }

    public function updateBackflag(Backflag $backflag): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->backflag = $backflag;
        return $package;
    }

    public function updateMaxbackiter(Maxbackiter $maxbackiter): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->maxbackiter = $maxbackiter;
        return $package;
    }

    public function updateBacktol(Backtol $backtol): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->backtol = $backtol;
        return $package;
    }

    public function updateBackreduce(Backreduce $backreduce): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->backreduce = $backreduce;
        return $package;
    }

    public function updateMaxinner(Maxinner $maxitinner): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->maxitinner = $maxitinner;
        return $package;
    }

    public function updateIlumethod(Ilumethod $ilumethod): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ilumethod = $ilumethod;
        return $package;
    }

    public function updateLevfill(Levfill $levfill): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->levfill = $levfill;
        return $package;
    }

    public function updateStoptol(Stoptol $stoptol): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->stoptol = $stoptol;
        return $package;
    }

    public function updateMsdr(Msdr $msdr): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->msdr = $msdr;
        return $package;
    }

    public function updateIacl(Iacl $iacl): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->iacl = $iacl;
        return $package;
    }

    public function updateNorder(Norder $norder): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->norder = $norder;
        return $package;
    }

    public function updateLevel(Level $level): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->level = $level;
        return $package;
    }

    public function updateNorth(North $north): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->north = $north;
        return $package;
    }

    public function updateIredsys(Iredsys $iredsys): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->iredsys = $iredsys;
        return $package;
    }

    public function updateRrctools(Rrctools $rrctols): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->rrctols = $rrctols;
        return $package;
    }

    public function updateIdroptol(Idroptol $idroptol): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->idroptol = $idroptol;
        return $package;
    }

    public function updateEpsrn(Epsrn $epsrn): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->epsrn = $epsrn;
        return $package;
    }

    public function updateHclosexmd(Hclosexmd $hclosexmd): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->hclosexmd = $hclosexmd;
        return $package;
    }

    public function updateMxiterxmd(Mxiterxmd $mxiterxmd): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->mxiterxmd = $mxiterxmd;
        return $package;
    }

    public function updateExtension(Extension $extension): NwtPackage
    {
        $package = self::fromArray($this->toArray());
        $package->extension = $extension;
        return $package;
    }

    public function updateUnitnumber(Unitnumber $unitnumber): NwtPackage
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
            'headtol' => $this->headtol->toFloat(),
            'fluxtol' => $this->fluxtol->toFloat(),
            'maxiterout' => $this->maxiterout->toInteger(),
            'thickfact' => $this->thickfact->toFloat(),
            'linmeth' => $this->linmeth->toInteger(),
            'iprnwt' => $this->iprnwt->toInteger(),
            'ibotavg' => $this->ibotavg->toInteger(),
            'options' => $this->options->toString(),
            'continue' => $this->continue->toBool(),
            'dbdtheta' => $this->dbdtheta->toFloat(),
            'dbdkappa' => $this->dbdkappa->toFloat(),
            'dbdgamma' => $this->dbdgamma->toFloat(),
            'momfact' => $this->momfact->toFloat(),
            'backflag' => $this->backflag->toInteger(),
            'maxbackiter' => $this->maxbackiter->toInteger(),
            'backtol' => $this->backtol->toFloat(),
            'backreduce' => $this->backreduce->toFloat(),
            'maxitinner' => $this->maxitinner->toInteger(),
            'ilumethod' => $this->ilumethod->toInteger(),
            'levfill' => $this->levfill->toInteger(),
            'stoptol' => $this->stoptol->toFloat(),
            'msdr' => $this->msdr->toInteger(),
            'iacl' => $this->iacl->toInteger(),
            'norder' => $this->norder->toInteger(),
            'level' => $this->level->toInteger(),
            'north' => $this->north->toInteger(),
            'iredsys' => $this->iredsys->toInteger(),
            'rrctols' => $this->rrctols->toFloat(),
            'idroptol' => $this->idroptol->toInteger(),
            'epsrn' => $this->epsrn->toFloat(),
            'hclosexmd' => $this->hclosexmd->toFloat(),
            'mxiterxmd' => $this->mxiterxmd->toInteger(),
            'extension' => $this->extension->toString(),
            'unitnumber' => $this->unitnumber->toInteger(),
        );
    }

    public function getEditables(): array
    {
        return array(
            'headtol' => $this->headtol->toFloat(),
            'fluxtol' => $this->fluxtol->toFloat(),
            'maxiterout' => $this->maxiterout->toInteger(),
            'thickfact' => $this->thickfact->toFloat(),
            'linmeth' => $this->linmeth->toInteger(),
            'iprnwt' => $this->iprnwt->toInteger(),
            'ibotavg' => $this->ibotavg->toInteger(),
            'options' => $this->options->toString(),
            'continue' => $this->continue->toBool(),
            'dbdtheta' => $this->dbdtheta->toFloat(),
            'dbdkappa' => $this->dbdkappa->toFloat(),
            'dbdgamma' => $this->dbdgamma->toFloat(),
            'momfact' => $this->momfact->toFloat(),
            'backflag' => $this->backflag->toInteger(),
            'maxbackiter' => $this->maxbackiter->toInteger(),
            'backtol' => $this->backtol->toFloat(),
            'backreduce' => $this->backreduce->toFloat(),
            'maxitinner' => $this->maxitinner->toInteger(),
            'ilumethod' => $this->ilumethod->toInteger(),
            'levfill' => $this->levfill->toInteger(),
            'stoptol' => $this->stoptol->toFloat(),
            'msdr' => $this->msdr->toInteger(),
            'iacl' => $this->iacl->toInteger(),
            'norder' => $this->norder->toInteger(),
            'level' => $this->level->toInteger(),
            'north' => $this->north->toInteger(),
            'iredsys' => $this->iredsys->toInteger(),
            'rrctols' => $this->rrctols->toFloat(),
            'idroptol' => $this->idroptol->toInteger(),
            'epsrn' => $this->epsrn->toFloat(),
            'hclosexmd' => $this->hclosexmd->toFloat(),
            'mxiterxmd' => $this->mxiterxmd->toInteger()
        );
    }

    public function mergeEditables(array $arr): void
    {
        $this->headtol = Headtol::fromFloat($arr['headtol']);
        $this->fluxtol = Fluxtol::fromFloat($arr['fluxtol']);
        $this->maxiterout = Maxiterout::fromInteger($arr['maxiterout']);
        $this->thickfact = Thickfact::fromFloat($arr['thickfact']);
        $this->linmeth = Linmeth::fromInteger($arr['linmeth']);
        $this->iprnwt = Iprnwt::fromInteger($arr['iprnwt']);
        $this->ibotavg = Ibotavg::fromInteger($arr['ibotavg']);
        $this->options = NwtOptions::fromString($arr['options']);
        $this->continue = NwtContinue::fromBool($arr['continue']);
        $this->dbdtheta = Dbdtheta::fromFloat($arr['dbdtheta']);
        $this->dbdkappa = Dbdkappa::fromFloat($arr['dbdkappa']);
        $this->dbdgamma = Dbdgamma::fromFloat($arr['dbdgamma']);
        $this->momfact = Momfact::fromFloat($arr['momfact']);
        $this->backflag = Backflag::fromInteger($arr['backflag']);
        $this->maxbackiter = Maxbackiter::fromInteger($arr['maxbackiter']);
        $this->backtol = Backtol::fromFloat($arr['backtol']);
        $this->backreduce = Backreduce::fromFloat($arr['backreduce']);
        $this->maxitinner = Maxinner::fromInteger($arr['maxitinner']);
        $this->ilumethod = Ilumethod::fromInteger($arr['ilumethod']);
        $this->levfill = Levfill::fromInteger($arr['levfill']);
        $this->stoptol = Stoptol::fromFloat($arr['stoptol']);
        $this->msdr = Msdr::fromInteger($arr['msdr']);
        $this->iacl = Iacl::fromInteger($arr['iacl']);
        $this->norder = Norder::fromInteger($arr['norder']);
        $this->level = Level::fromInteger($arr['level']);
        $this->north = North::fromInteger($arr['north']);
        $this->iredsys = Iredsys::fromInteger($arr['iredsys']);
        $this->rrctols = Rrctools::fromFloat($arr['rrctols']);
        $this->idroptol = Idroptol::fromInteger($arr['idroptol']);
        $this->epsrn = Epsrn::fromFloat($arr['epsrn']);
        $this->hclosexmd = Hclosexmd::fromFloat($arr['hclosexmd']);
        $this->mxiterxmd = Mxiterxmd::fromInteger($arr['mxiterxmd']);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
