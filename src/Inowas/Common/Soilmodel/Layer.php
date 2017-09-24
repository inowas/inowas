<?php

declare(strict_types=1);

namespace Inowas\Common\Soilmodel;

use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Modflow\Botm;
use Inowas\Common\Modflow\Chani;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\Hani;
use Inowas\Common\Modflow\Hk;
use Inowas\Common\Modflow\Layavg;
use Inowas\Common\Modflow\Laytyp;
use Inowas\Common\Modflow\Layvka;
use Inowas\Common\Modflow\Laywet;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Modflow\Ss;
use Inowas\Common\Modflow\Sy;
use Inowas\Common\Modflow\Top;
use Inowas\Common\Modflow\Vka;
use Inowas\Common\Modflow\Wetdry;

class Layer
{
    /** @var  LayerId */
    private $id;

    /** @var  Name */
    private $name;

    /** @var  Description */
    private $description;

    /** @var  LayerNumber */
    private $number;

    /** @var  Top */
    private $top;

    /** @var  Botm */
    private $botm;

    /** @var  Hk */
    private $hk;

    /**
     * @var  Chani
     *
     * The documentation says:
     * If CHANI is less than or equal to 0, then variable HANI
     * defines horizontal anisotropy.
     *
     * By default we will set chani to 0 and apply calculation
     * of horizontal anisotropy with hani
     */
    private $chani;

    /** @var  Hani */
    private $hani;

    /**
     * @var  Layvka
     *
     * The documentation says:
     * Flag for each layer that indicates whether variable VKA is vertical
     * hydraulic conductivity or the ratio of horizontal to vertical hydraulic conductivity.
     * 0—indicates VKA is vertical hydraulic conductivity
     * not 0—indicates VKA is the ratio of horizontal to vertical hydraulic conductivity,
     * where the horizontal hydraulic conductivity is specified as HK in item 10.
     *
     * By default we will set layvka to 0
     * -> and set vertical hydraulic conductivity as absolute value
     */
    private $layvka;

    /** @var  Vka */
    private $vka;

    /** @var  Layavg */
    private $layavg;

    /** @var  Laytyp */
    private $laytyp;

    /** @var  Laywet */
    private $laywet;

    /**
     * @var Wetdry
     *
     * From the documentation:
     *
     * is a combination of the wetting threshold and a flag to indicate
     * which neighboring cells can cause a cell to become wet.
     * (default is -0.01).
     *
     * I'll leave this by default as it is without option to change it.
     * The user can't change this value at the moment.
     */
    private $wetdry;

    /**
     * @var  Ss
     *
     * From the definition:
     * is specific storage unless the STORAGECOEFFICIENT option is used
     * We won't use the STORAGECOEFFICIENT-Option, so it is always specific storage!
     */
    private $ss;

    /** @var  Sy */
    private $sy;

    public static function fromParams(
        LayerId $id,
        Name $name,
        Description $description,
        LayerNumber $number,
        ?Top $top,
        Botm $botm,
        Hk $hk,
        Hani $hani,
        Vka $vka,
        Layavg $layavg,
        Laytyp $laytyp,
        Laywet $laywet,
        Ss $ss,
        Sy $sy
    ): Layer
    {
        $self = new self();
        $self->chani = Chani::fromFloat(0);
        $self->layvka = Layvka::fromFloat(0);
        $self->wetdry = Wetdry::fromFloat(-0.01);

        $self->id = $id;
        $self->name = $name;
        $self->description = $description;
        $self->number = $number;
        $self->top = $top;
        $self->botm = $botm;
        $self->hk = $hk;
        $self->hani = $hani;
        $self->vka = $vka;
        $self->layavg = $layavg;
        $self->laytyp = $laytyp;
        $self->laywet = $laywet;
        $self->ss = $ss;
        $self->sy = $sy;
        return $self;
    }

    public static function fromArray(array $arr): Layer
    {
        $self = new self();
        $self->chani = Chani::fromFloat(0);
        $self->layvka = Layvka::fromFloat(0);
        $self->wetdry = Wetdry::fromFloat(-0.01);

        $self->id = LayerId::fromString($arr['id']);
        $self->name = Name::fromString($arr['name']);
        $self->description = Description::fromString($arr['description']);
        $self->number = LayerNumber::fromInt($arr['number']);
        $self->top = null !== $arr['top'] ? Top::fromValue($arr['top']) : null;
        $self->botm = Botm::fromValue($arr['botm']);
        $self->hk = Hk::fromValue($arr['hk']);
        $self->hani = Hani::fromValue($arr['hani']);
        $self->vka = Vka::fromValue($arr['vka']);
        $self->layavg = Layavg::fromInt($arr['layavg']);
        $self->laytyp = Laytyp::fromInt($arr['laytyp']);
        $self->laywet = Laywet::fromFloat($arr['laywet']);
        $self->ss = Ss::fromValue($arr['ss']);
        $self->sy = Sy::fromValue($arr['sy']);
        return $self;
    }

    private function __construct(){}

    public function toArray(): array
    {
        return array(
            'id' => $this->id->toString(),
            'name' => $this->name->toString(),
            'description' => $this->description->toString(),
            'number' => $this->number->toInt(),
            'top' => null === $this->top ? null : $this->top->toValue(),
            'botm' => $this->botm->toValue(),
            'hk' => $this->hk->toValue(),
            'hani' => $this->hani->toValue(),
            'vka' => $this->vka->toValue(),
            'layavg' => $this->layavg->toInt(),
            'laytyp' => $this->laytyp->toInt(),
            'laywet' => $this->laywet->toFloat(),
            'ss' => $this->ss->toValue(),
            'sy' => $this->sy->toValue()
        );
    }

    public function toMetadataArray(): array
    {
        return array(
            'id' => $this->id->toString(),
            'name' => $this->name->toString(),
            'description' => $this->description->toString(),
            'number' => $this->number->toInt(),
            'laytyp' => $this->laytyp->toInt()
        );
    }

    public function id(): LayerId
    {
        return $this->id;
    }

    public function number(): LayerNumber
    {
        return $this->number;
    }

    /**
     * @return Name
     */
    public function name(): Name
    {
        return $this->name;
    }

    /**
     * @return Description
     */
    public function description(): Description
    {
        return $this->description;
    }

    /**
     * @return Top
     */
    public function top(): Top
    {
        return $this->top;
    }

    /**
     * @return Botm
     */
    public function botm(): Botm
    {
        return $this->botm;
    }

    /**
     * @return Hk
     */
    public function hk(): Hk
    {
        return $this->hk;
    }

    /**
     * @return Chani
     */
    public function chani(): Chani
    {
        return $this->chani;
    }

    /**
     * @return Hani
     */
    public function hani(): Hani
    {
        return $this->hani;
    }

    /**
     * @return Layvka
     */
    public function layvka(): Layvka
    {
        return $this->layvka;
    }

    /**
     * @return Vka
     */
    public function vka(): Vka
    {
        return $this->vka;
    }

    /**
     * @return Layavg
     */
    public function layavg(): Layavg
    {
        return $this->layavg;
    }

    /**
     * @return Laytyp
     */
    public function laytyp(): Laytyp
    {
        return $this->laytyp;
    }

    /**
     * @return Laywet
     */
    public function laywet(): Laywet
    {
        return $this->laywet;
    }

    /**
     * @return Wetdry
     */
    public function wetdry(): Wetdry
    {
        return $this->wetdry;
    }

    /**
     * @return Ss
     */
    public function ss(): Ss
    {
        return $this->ss;
    }

    /**
     * @return Sy
     */
    public function sy(): Sy
    {
        return $this->sy;
    }

    public function hash(): string
    {
        $data = $this->toArray();
        $this->recursiveKeySort($data);
        return md5(json_encode($data));
    }

    /**
     * @param $by_ref_array
     */
    private function recursiveKeySort(&$by_ref_array): void
    {
        ksort($by_ref_array, SORT_NUMERIC );
        foreach ($by_ref_array as $key => $value) {
            if (is_array($value)) {
                $this->recursiveKeySort($by_ref_array[$key]);
            }
        }
    }
}
