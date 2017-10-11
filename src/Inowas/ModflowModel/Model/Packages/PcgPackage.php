<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

use Inowas\Common\Modflow\Damp;
use Inowas\Common\Modflow\Dampt;
use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\Hclose;
use Inowas\Common\Modflow\Ihcofadd;
use Inowas\Common\Modflow\Iprpcg;
use Inowas\Common\Modflow\Iter1;
use Inowas\Common\Modflow\Mutpcg;
use Inowas\Common\Modflow\Mxiter;
use Inowas\Common\Modflow\Nbpol;
use Inowas\Common\Modflow\Npcond;
use Inowas\Common\Modflow\Rclose;
use Inowas\Common\Modflow\Relax;
use Inowas\Common\Modflow\Unitnumber;

class PcgPackage extends AbstractPackage
{
    const TYPE = 'pcg';
    const DESCRIPTION = 'Preconditioned Conjugate-Gradient Package';

    /** @var Mxiter  */
    protected $mxiter;

    /** @var  Iter1 */
    protected $iter1;

    /** @var  Npcond */
    protected $npcond;

    /** @var Hclose  */
    protected $hclose;

    /** @var  Rclose */
    protected $rclose;

    /** @var  Relax */
    protected $relax;

    /** @var  Nbpol */
    protected $nbpol;

    /** @var Iprpcg  */
    protected $iprpcg;

    /** @var  Mutpcg */
    protected $mutpcg;

    /** @var  Damp */
    protected $damp;

    /** @var  Dampt */
    protected $dampt;

    /** @var  Ihcofadd */
    protected $ihcofadd;

    /** @var  Extension */
    protected $extension;

    /** @var  Unitnumber */
    protected $unitnumber;

    /**
     * @return PcgPackage
     */
    public static function fromDefaults(): PcgPackage
    {
        $mxiter = Mxiter::fromInteger(50);
        $iter1 = Iter1::fromInteger(30);
        $npcond = Npcond::fromInteger(1);
        $hclose = Hclose::fromFloat(1e-2);
        $rclose = Rclose::fromFloat(1e-2);
        $relax = Relax::fromFloat(1.0);
        $nbpol = Nbpol::fromInteger(0);
        $iprpcg = Iprpcg::fromInteger(0);
        $mutpcg = Mutpcg::fromInteger(3);
        $damp = Damp::fromFloat(1.0);
        $dampt = Dampt::fromFloat(1.0);
        $ihcofadd = Ihcofadd::fromInteger(0);
        $extension = Extension::fromString('pcg');
        $unitnumber = Unitnumber::fromInteger(27);

        return new self(
            $mxiter, $iter1, $npcond, $hclose, $rclose, $relax,
            $nbpol, $iprpcg, $mutpcg, $damp, $dampt, $ihcofadd,
            $extension, $unitnumber
        );
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param Mxiter $mxiter
     * @param Iter1 $iter1
     * @param Npcond $npcond
     * @param Hclose $hclose
     * @param Rclose $rclose
     * @param Relax $relax
     * @param Nbpol $nbpol
     * @param Iprpcg $iprpcg
     * @param Mutpcg $mutpcg
     * @param Damp $damp
     * @param Dampt $dampt
     * @param Ihcofadd $ihcofadd
     * @param Extension $extension
     * @param Unitnumber $unitnumber
     * @return PcgPackage
     */
    public static function fromParams(
        Mxiter $mxiter,
        Iter1 $iter1,
        Npcond $npcond,
        Hclose $hclose,
        Rclose $rclose,
        Relax $relax,
        Nbpol $nbpol,
        Iprpcg $iprpcg,
        Mutpcg $mutpcg,
        Damp $damp,
        Dampt $dampt,
        Ihcofadd $ihcofadd,
        Extension $extension,
        Unitnumber $unitnumber
    ): PcgPackage
    {
        return new self(
            $mxiter, $iter1, $npcond, $hclose, $rclose, $relax,
            $nbpol, $iprpcg, $mutpcg, $damp, $dampt, $ihcofadd,
            $extension, $unitnumber
        );
    }

    private function __construct(
        Mxiter $mxiter,
        Iter1 $iter1,
        Npcond $npcond,
        Hclose $hclose,
        Rclose $rclose,
        Relax $relax,
        Nbpol $nbpol,
        Iprpcg $iprpcg,
        Mutpcg $mutpcg,
        Damp $damp,
        Dampt $dampt,
        Ihcofadd $ihcofadd,
        Extension $extension,
        Unitnumber $unitnumber
    )
    {
        $this->mxiter = $mxiter;
        $this->iter1 = $iter1;
        $this->npcond = $npcond;
        $this->hclose = $hclose;
        $this->rclose = $rclose;
        $this->relax = $relax;
        $this->nbpol = $nbpol;
        $this->iprpcg = $iprpcg;
        $this->mutpcg = $mutpcg;
        $this->damp = $damp;
        $this->dampt = $dampt;
        $this->ihcofadd = $ihcofadd;
        $this->extension = $extension;
        $this->unitnumber = $unitnumber;
    }

    public static function fromArray(array $arr): PcgPackage
    {
        $mxiter = Mxiter::fromInteger($arr['mxiter']);
        $iter1 = Iter1::fromInteger($arr['iter1']);
        $npcond = Npcond::fromInteger($arr['npcond']);
        $hclose = Hclose::fromFloat($arr['hclose']);
        $rclose = Rclose::fromFloat($arr['rclose']);
        $relax = Relax::fromFloat($arr['relax']);
        $nbpol = Nbpol::fromInteger($arr['nbpol']);
        $iprpcg = Iprpcg::fromInteger($arr['iprpcg']);
        $mutpcg = Mutpcg::fromInteger($arr['mutpcg']);
        $damp = Damp::fromFloat($arr['damp']);
        $dampt = Dampt::fromFloat($arr['dampt']);
        $ihcofadd = Ihcofadd::fromInteger($arr['ihcofadd']);
        $extension = Extension::fromString($arr['extension']);
        $unitnumber = Unitnumber::fromInteger($arr['unitnumber']);

        return new self(
            $mxiter, $iter1, $npcond, $hclose, $rclose, $relax,
            $nbpol, $iprpcg, $mutpcg, $damp, $dampt, $ihcofadd,
            $extension, $unitnumber
        );
    }

    public function updateMxiter(Mxiter $mxiter): PcgPackage
    {
        $package = self::fromArray($this->toArray());
        $package->mxiter = $mxiter;
        return $package;
    }

    public function updateIter1(Iter1 $iter1): PcgPackage
    {
        $package = self::fromArray($this->toArray());
        $package->iter1 = $iter1;
        return $package;
    }

    public function updateNpcond(Npcond $npcond): PcgPackage
    {
        $package = self::fromArray($this->toArray());
        $package->npcond = $npcond;
        return $package;
    }

    public function updateHclose(Hclose $hclose): PcgPackage
    {
        $package = self::fromArray($this->toArray());
        $package->hclose = $hclose;
        return $package;
    }

    public function updateRclose(Rclose $rclose): PcgPackage
    {
        $package = self::fromArray($this->toArray());
        $package->rclose = $rclose;
        return $package;
    }

    public function updateRelax(Relax $relax): PcgPackage
    {
        $package = self::fromArray($this->toArray());
        $package->relax = $relax;
        return $package;
    }

    public function updateNbpol(Nbpol $nbpol): PcgPackage
    {
        $package = self::fromArray($this->toArray());
        $package->nbpol = $nbpol;
        return $package;
    }

    public function updateIprpcg(Iprpcg $iprpcg): PcgPackage
    {
        $package = self::fromArray($this->toArray());
        $package->iprpcg = $iprpcg;
        return $package;
    }

    public function updateMutpcg(Mutpcg $mutpcg): PcgPackage
    {
        $package = self::fromArray($this->toArray());
        $package->mutpcg = $mutpcg;
        return $package;
    }

    public function updateDamp(Damp $damp): PcgPackage
    {
        $package = self::fromArray($this->toArray());
        $package->damp = $damp;
        return $package;
    }

    public function updateDampt(Dampt $dampt): PcgPackage
    {
        $package = self::fromArray($this->toArray());
        $package->dampt = $dampt;
        return $package;
    }

    public function updateIhcofadd(Ihcofadd $ihcofadd): PcgPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ihcofadd = $ihcofadd;
        return $package;
    }

    public function updateExtension(Extension $extension): PcgPackage
    {
        $package = self::fromArray($this->toArray());
        $package->extension = $extension;
        return $package;
    }

    public function updateUnitnumber(Unitnumber $unitnumber): PcgPackage
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
            'mxiter' => $this->mxiter->toInteger(),
            'iter1' => $this->iter1->toInteger(),
            'npcond' => $this->npcond->toInteger(),
            'hclose' => $this->hclose->toFloat(),
            'rclose' => $this->rclose->toFloat(),
            'relax' => $this->relax->toFloat(),
            'nbpol' => $this->nbpol->toInteger(),
            'iprpcg' => $this->iprpcg->toInteger(),
            'mutpcg' => $this->mutpcg->toInteger(),
            'damp' => $this->damp->toFloat(),
            'dampt' => $this->dampt->toFloat(),
            'ihcofadd' => $this->ihcofadd->toInteger(),
            'extension' => $this->extension->toString(),
            'unitnumber' => $this->unitnumber->toInteger()
        );
    }

    public function getEditables(): array
    {
        return array(
            'mxiter' => $this->mxiter->toInteger(),
            'iter1' => $this->iter1->toInteger(),
            'npcond' => $this->npcond->toInteger(),
            'hclose' => $this->hclose->toFloat(),
            'rclose' => $this->rclose->toFloat(),
            'relax' => $this->relax->toFloat(),
            'nbpol' => $this->nbpol->toInteger(),
            'iprpcg' => $this->iprpcg->toInteger(),
            'mutpcg' => $this->mutpcg->toInteger(),
            'damp' => $this->damp->toFloat(),
            'dampt' => $this->dampt->toFloat(),
            'ihcofadd' => $this->ihcofadd->toInteger()
        );
    }

    public function mergeEditables(array $arr): void
    {
        $this->mxiter = Mxiter::fromInteger($arr['mxiter']);
        $this->iter1 = Iter1::fromInteger($arr['iter1']);
        $this->npcond = Npcond::fromInteger($arr['npcond']);
        $this->hclose = Hclose::fromFloat($arr['hclose']);
        $this->rclose = Rclose::fromFloat($arr['rclose']);
        $this->relax = Relax::fromFloat($arr['relax']);
        $this->nbpol = Nbpol::fromInteger($arr['nbpol']);
        $this->iprpcg = Iprpcg::fromInteger($arr['iprpcg']);
        $this->mutpcg = Mutpcg::fromInteger($arr['mutpcg']);
        $this->damp = Damp::fromFloat($arr['damp']);
        $this->dampt = Dampt::fromFloat($arr['dampt']);
        $this->ihcofadd = Ihcofadd::fromInteger($arr['ihcofadd']);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
