<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\Hnoflo;
use Inowas\Common\Modflow\Ibound;
use Inowas\Common\Modflow\IchFlg;
use Inowas\Common\Modflow\Ixsec;
use Inowas\Common\Modflow\Stoper;
use Inowas\Common\Modflow\Strt;
use Inowas\Common\Modflow\Unitnumber;

class BasPackage extends AbstractPackage
{

    const TYPE = 'bas';
    const DESCRIPTION = 'Basic Package';

    /** @var  Ibound */
    protected $ibound;

    /** @var  Strt */
    protected $strt;

    /** @var Ixsec */
    protected $ixsec;

    /** @var IchFlg */
    protected $ichflg;

    /** @var Stoper */
    protected $stoper;

    /** @var Hnoflo */
    protected $hnoflo;

    /** @var Extension */
    protected $extension;

    /** @var Unitnumber  */
    protected $unitnumber;

    public static function fromDefaults(): BasPackage
    {
        $iBound = Ibound::fromValue(1);
        $strt = Strt::fromValue(1.0);
        $ixsec = Ixsec::fromBool(false);
        $ichflg = IchFlg::fromBool(false);
        $stoper = Stoper::none();
        $hnoflo = Hnoflo::fromFloat(-999.99);
        $extension = Extension::fromString('bas');
        $unitnumber = Unitnumber::fromInteger(13);

        $self = new self();
        $self->ibound = $iBound;
        $self->strt = $strt;
        $self->ixsec = $ixsec;
        $self->ichflg = $ichflg;
        $self->stoper = $stoper;
        $self->hnoflo = $hnoflo;
        $self->extension = $extension;
        $self->unitnumber = $unitnumber;
        return $self;
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param Ibound $ibound
     * @param Strt $strt
     * @param Ixsec $ixsec
     * @param IchFlg $ichFlg
     * @param Stoper $stoPer
     * @param Hnoflo $hnoFlo
     * @param Extension $extension
     * @param Unitnumber $unitNumber
     * @return BasPackage
     */
    public static function fromParams(
        Ibound $ibound,
        Strt $strt,
        Ixsec $ixsec,
        IchFlg $ichFlg,
        Stoper $stoPer,
        Hnoflo $hnoFlo,
        Extension $extension,
        Unitnumber $unitNumber
    ): BasPackage
    {
        $self = new self();
        $self->ibound = $ibound;
        $self->strt = $strt;
        $self->ixsec = $ixsec;
        $self->ichflg = $ichFlg;
        $self->stoper = $stoPer;
        $self->hnoflo = $hnoFlo;
        $self->extension = $extension;
        $self->unitnumber = $unitNumber;

        return $self;
    }

    public static function fromArray(array $arr): BasPackage
    {
        $self = new self();
        $self->ibound = Ibound::fromValue($arr['ibound']);
        $self->strt = Strt::fromValue($arr['strt']);
        $self->ixsec = Ixsec::fromValue($arr['ixsec']);
        $self->ichflg = IchFlg::fromValue($arr['ichflg']);
        $self->stoper = Stoper::fromValue($arr['stoper']);
        $self->hnoflo = Hnoflo::fromFloat($arr['hnoflo']);
        $self->extension = Extension::fromValue($arr['extension']);
        $self->unitnumber = Unitnumber::fromValue($arr['unitnumber']);

        return $self;
    }

    public function iBound(): Ibound
    {
        return $this->ibound;
    }

    public function strt(): Strt
    {
        return $this->strt;
    }

    public function ixsec(): Ixsec
    {
        return $this->ixsec;
    }

    public function ichflg(): IchFlg
    {
        return $this->ichflg;
    }

    public function stoper(): Stoper
    {
        return $this->stoper;
    }

    public function hnoflo(): Hnoflo
    {
        return $this->hnoflo;
    }

    public function extension(): Extension
    {
        return $this->extension;
    }

    public function unitnumber(): Unitnumber
    {
        return $this->unitnumber;
    }

    public function updateIBound(Ibound $iBound): BasPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ibound = $iBound;
        return $package;
    }

    public function updateStrt(Strt $strt): BasPackage
    {
        $package = self::fromArray($this->toArray());
        $package->strt = $strt;
        return $package;
    }

    public function updateIxsec(Ixsec $ixsec): BasPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ixsec = $ixsec;
        return $package;
    }

    public function updateIchflg(IchFlg $ichFlg): BasPackage
    {
        $package = self::fromArray($this->toArray());
        $package->ichflg = $ichFlg;
        return $package;
    }

    public function updateStoper(Stoper $stoper): BasPackage
    {
        $package = self::fromArray($this->toArray());
        $package->stoper = $stoper;
        return $package;
    }

    public function updateHnoflo(Hnoflo $hnoflo): BasPackage
    {
        $package = self::fromArray($this->toArray());
        $package->hnoflo = $hnoflo;
        return $package;
    }

    public function updateExtension(Extension $extension): BasPackage
    {
        $package = self::fromArray($this->toArray());
        $package->extension = $extension;
        return $package;
    }

    public function updateUnitnumber(Unitnumber $unitnumber): BasPackage
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
            'ibound' => $this->ibound->toValue(),
            'strt' => $this->strt->toValue(),
            'ixsec' => $this->ixsec->toValue(),
            'ichflg' => $this->ichflg->toValue(),
            'stoper' => $this->stoper->toValue(),
            'hnoflo' => $this->hnoflo->toValue(),
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
