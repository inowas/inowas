<?php
/**
 * rech : float or array of floats (nrow, ncol)
 * is the recharge flux. (default is 1.e-3).
 */
declare(strict_types=1);

namespace Inowas\Common\Modflow;

use Inowas\Common\Grid\ActiveCells;

class Rech
{

    /**
     * @var array|float
     */
    protected $value;

    public static function from2DArray(array $value): Rech
    {
        $self = new self();
        $self->value = $value;
        return $self;
    }

    public static function fromActiveCellsAndValue(ActiveCells $activeCells, float $rech): Rech
    {
        $cells = $activeCells->cells();
        $value = array();
        foreach ($cells as $rowNumber => $rows){
            $value[$rowNumber] = array();
            foreach ($rows as $colNumber => $value){
                if ($value){
                    $value[$rowNumber][$colNumber] = $rech;
                }
            }
        }

        $self = new self();
        $self->value = $value;
        return $self;
    }

    public static function fromFloat(float $value): Rech
    {
        $self = new self();
        $self->value = $value;
        return $self;
    }

    public static function fromValue($value): Rech
    {
        $self = new self();
        $self->value = $value;
        return $self;
    }

    private function __construct(){}

    public function toValue()
    {
        return $this->value;
    }

    public function to2DArray(): array
    {
        if (is_array($this->value) && is_array($this->value[0])) {
            return $this->value;
        }

        return null;
    }
}
