<?php
/**
 * steady : boolean or array of boolean (nper)
 * true or false indicating whether or not stress period is steady state
 * (default is true).
 */
declare(strict_types=1);

namespace Inowas\Common\Modflow;

class Steady
{
    protected $steady;

    public static function fromArray(array $steady): Steady
    {
        $self = new self();
        $self->steady = $steady;
        return $self;
    }

    public static function fromValue($steady): Steady
    {
        $self = new self();
        $self->steady = $steady;
        return $self;
    }

    public function toValue()
    {
        return $this->steady;
    }

    public function isArray(): bool
    {
        return is_array($this->steady);
    }
}
