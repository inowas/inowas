<?php

declare(strict_types=1);

namespace Inowas\Common\Modflow;

use Inowas\Common\Id\CalculationId;

final class Results
{
    /** @var CalculationId */
    private $id;

    /** @var LayerValues  */
    private $layerValues;

    /** @var TotalTimes  */
    private $times;

    public static function create(CalculationId $id, LayerValues $layerValues, TotalTimes $times): Results
    {
        return new self($id, $layerValues, $times);
    }

    private function __construct(CalculationId $id, LayerValues $layerValues, TotalTimes $times) {
        $this->id = $id;
        $this->layerValues = $layerValues;
        $this->times = $times;
    }

    public function toArray(): array
    {
        return array(
            'calculation_id' => $this->id->toString(),
            'layer_values' => $this->layerValues->toArray(),
            'times' => $this->times->toArray()
        );
    }
}
