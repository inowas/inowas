<?php

namespace Inowas\Common\Soilmodel;

use Inowas\Common\Modflow\Constantcv;
use Inowas\Common\Modflow\Hdry;
use Inowas\Common\Modflow\Ihdwet;
use Inowas\Common\Modflow\Ipakcb;
use Inowas\Common\Modflow\Iphdry;
use Inowas\Common\Modflow\Iwetit;
use Inowas\Common\Modflow\Nocvcorrection;
use Inowas\Common\Modflow\Novfc;
use Inowas\Common\Modflow\Storagecoefficient;
use Inowas\Common\Modflow\Thickstrt;
use Inowas\Common\Modflow\Vkcb;
use Inowas\Common\Modflow\Wetfct;


final class Soilmodel
{

    /**
     * @var Ipakcb
     *
     * From the documentation:
     *
     * A flag that is used to determine if cell-by-cell budget data should be
     * saved. If ipakcb is non-zero cell-by-cell budget data will be saved.
     * (default is 53)
     *
     * As I'm not sure for what the should use this.
     * I'll leave this by default as it is without option to change it.
     * The user can't change this value at the moment.
     */
    private $ipakcb;

    /**
     * @var  Hdry
     *
     * From the documentation:
     *
     * Is the head that is assigned to cells that are converted to dry during a simulation.
     * Although this value plays no role in the model calculations.
     * HDRY is thus similar to HNOFLO in the Basic Package, which is the value assigned to cells
     * that are no-flow cells at the start of a model simulation.
     * (default is -1.e30).
     *
     * I'll leave this by default as it is without option to change it.
     * The user can't change this value at the moment.
     */
    private $hdry;

    /** @var Wetfct */
    private $wetfct;

    /**
     * @var Iwetit
     *
     * From the documentation:
     *
     * (default is 1)
     * As I'm not sure for what the should use this.
     * I'll leave this by default as it is without option to change it.
     * The user can't change this value at the moment.
     */
    private $iwetit;

    /**
     * @var Ihdwet
     *
     * From the documentation:
     *
     * (default is 0)
     * As I'm not sure for what the should use this.
     * I'll leave this by default as it is without option to change it.
     * The user can't change this value at the moment.
     */
    private $ihdwet;

    /**
     * @var Vkcb
     *
     * From the documentation:
     *
     * is the vertical hydraulic conductivity of a Quasi-three-dimensional
     * confining bed below a layer.
     * (default is 0.0).
     *
     * I'll leave this by default as it is without option to change it.
     * The user can't change this value at the moment.
     */
    private $vkcb;

    /**
     * @var Storagecoefficient
     *
     * From the documentation:
     *
     * indicates that variable Ss and SS parameters are read as storage
     * coefficient rather than specific storage. (default is False).
     *
     * We won't use the STORAGECOEFFICIENT-Option, so it is always specific storage!
     * So, the default will be false, always.
     */
    private $storagecoeficient;

    /**
     * @var Constantcv
     *
     * From the documentation:
     *
     * indicates that vertical conductance for an unconfined cell is
     * computed from the cell thickness rather than the saturated thickness.
     * The CONSTANTCV option automatically invokes the NOCVCORRECTION
     * option. (default is False).
     *
     * I'll leave this by default as it is without option to change it.
     * The user can't change this value at the moment.
     */
    private $constantcv;

    /**
     * @var Thickstrt
     *
     * From the documentation:
     *
     * (default is False)
     *
     * I'll leave this by default as it is without option to change it.
     * The user can't change this value at the moment.
     */
    private $thickstrt;

    /**
     * @var Nocvcorrection
     *
     * From the documentation:
     *
     * indicates that vertical conductance is not corrected when the vertical
     * flow correction is applied. (default is False).
     *
     * I'll leave this by default as it is without option to change it.
     * The user can't change this value at the moment.
     */
    private $nocvcorrection;

    /**
     * @var Novfc
     *
     * From the documentation:
     *
     * (default is False).
     *
     * I'll leave this by default as it is without option to change it.
     * The user can't change this value at the moment.
     */
    private $novfc;

    /**
     * @var Iphdry
     *
     * From the documentation:
     *
     * (default is 0).
     *
     * I'll leave this by default as it is without option to change it.
     * The user can't change this value at the moment.
     */
    private $iphdry;

    public static function fromDefaults(): Soilmodel
    {
        $self = new self();
        $self->ipakcb = Ipakcb::fromInteger(53);
        $self->hdry = Hdry::fromFloat(-1.e30);
        $self->iwetit = Iwetit::fromInteger(1);
        $self->ihdwet = Ihdwet::fromInteger(0);
        $self->vkcb = Vkcb::fromFloat(0.0);
        $self->storagecoeficient = Storagecoefficient::fromBool(false);
        $self->constantcv = Constantcv::fromBool(false);
        $self->thickstrt = Thickstrt::fromBool(false);
        $self->nocvcorrection = Nocvcorrection::fromBool(false);
        $self->novfc = Novfc::fromBool(false);
        $self->iphdry = Iphdry::fromInt(0);

        $self->wetfct = Wetfct::fromFloat(0.1);
        return $self;
    }

    public static function fromArray(array $arr): Soilmodel
    {
        $self = self::fromDefaults();
        $self->wetfct = Wetfct::fromFloat($arr['wetfct']);
        return $self;
    }

    private function __construct(){}

    public function toArray(): array
    {
        return array(
            'wetfct' => $this->wetfct->toFloat()
        );
    }

    /**
     * @return Ipakcb
     */
    public function ipakcb(): Ipakcb
    {
        return $this->ipakcb;
    }

    /**
     * @return Hdry
     */
    public function hdry(): Hdry
    {
        return $this->hdry;
    }

    /**
     * @return Wetfct
     */
    public function wetfct(): Wetfct
    {
        return $this->wetfct;
    }

    /**
     * @return Iwetit
     */
    public function iwetit(): Iwetit
    {
        return $this->iwetit;
    }

    /**
     * @return Ihdwet
     */
    public function ihdwet(): Ihdwet
    {
        return $this->ihdwet;
    }

    /**
     * @return Vkcb
     */
    public function vkcb(): Vkcb
    {
        return $this->vkcb;
    }

    /**
     * @return Storagecoefficient
     */
    public function storagecoeficient(): Storagecoefficient
    {
        return $this->storagecoeficient;
    }

    /**
     * @return Constantcv
     */
    public function constantcv(): Constantcv
    {
        return $this->constantcv;
    }

    /**
     * @return Thickstrt
     */
    public function thickstrt(): Thickstrt
    {
        return $this->thickstrt;
    }

    /**
     * @return Nocvcorrection
     */
    public function nocvcorrection(): Nocvcorrection
    {
        return $this->nocvcorrection;
    }

    /**
     * @return Novfc
     */
    public function novfc(): Novfc
    {
        return $this->novfc;
    }

    /**
     * @return Iphdry
     */
    public function iphdry(): Iphdry
    {
        return $this->iphdry;
    }
}
