<?php
/**
 * IBOUND—is the boundary variable. One value is read for every model cell.
 * Usually, these values are read a layer at a time; however, when the XSECTION
 * option is specified, a single variable for the cross section is read.
 * Note that although IBOUND is read as one or more two-dimensional variables,
 * it is stored internally as a three-dimensional variable.
 *
 * If IBOUND(J,I,K) < 0, cell J,I,K has a constant head.
 * If IBOUND(J,I,K) = 0, cell J,I,K is inactive.
 * If IBOUND(J,I,K) > 0, cell J,I,K is active.
 */
declare(strict_types=1);

namespace Inowas\Common\Modflow;

use Inowas\Common\Grid\ActiveCells;

class Ibound
{
    
    /** @var  array */
    protected $ibound;

    public static function from3DArray(array $ibound): Ibound
    {
        $self = new self();
        $self->ibound = $ibound;
        return $self;
    }

    public static function fromActiveCellsAndNumberOfLayers(ActiveCells $activeCells, int $numberOfLayers): Ibound
    {
        $arr = [];
        foreach ($activeCells->to2DArray() as $rowNumber => $row){
            $arr[$rowNumber] = [];
            foreach ($row as $colNumber => $value){
                if ($value === false){
                    $value = 0;
                } else {
                    $value = 1;
                }

                $arr[$rowNumber][$colNumber] = $value;
            }
        }

        $self = new self();
        $iBound = [];
        for ($i=0; $i<$numberOfLayers; $i++){
            $iBound[$i] = $arr;
        }
        $self->ibound = $iBound;
        return $self;
    }

    public static function fromValue($ibound): Ibound
    {
        $self = new self();
        $self->ibound = $ibound;
        return $self;
    }

    private function __construct(){}

    public function toValue()
    {
        return $this->ibound;
    }
}
