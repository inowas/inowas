<?php
/**
 * laywet : float or array of floats (nlay)
 * contains a flag for each layer that indicates if wetting is active.
 * 0: indicates wetting is inactive
 * not 0: indicates wetting is active
 **/
declare(strict_types=1);

namespace Inowas\Common\Modflow;

class Laywet
{

    CONST WETTING_ACTIVE = 1;
    CONST WETTING_INACTIVE = 0;


    protected $laywet;

    public static function fromArray(array $laywet): Laywet
    {
        $self = new self();
        $self->laywet = $laywet;
        return $self;
    }

    public static function fromFloat(float $laywet): Laywet
    {
        $self = new self();
        $self->laywet = $laywet;
        return $self;
    }

    public static function fromValue($laywet): Laywet
    {
        $self = new self();
        $self->laywet = $laywet;
        return $self;
    }

    public function toValue()
    {
        return $this->laywet;
    }

    public function toFloat(): float
    {
        return $this->laywet;
    }

    public function isArray()
    {
        return is_array($this->laywet);
    }
}
