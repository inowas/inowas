<?php

declare(strict_types=1);

namespace Inowas\Common\Calculation;

use Inowas\Common\Grid\Ncol;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Grid\Nrow;
use Inowas\Common\Calculation\ResultType;

class TimeSeries implements \JsonSerializable
{

    /** @var  ResultType */
    protected $type;

    /** @var  LayerNumber */
    protected $layer;

    /** @var  Nrow */
    protected $row;

    /** @var  Ncol */
    private $column;

    /** @var  TimeSeriesData */
    protected $data;

    public static function fromParameters(
        ResultType $type,
        LayerNumber $layerNumber,
        Nrow $row,
        Ncol $column,
        TimeSeriesData $data
    ): TimeSeries
    {
        $self = new self();
        $self->type = $type;
        $self->layer = $layerNumber;
        $self->row = $row;
        $self->column = $column;
        $self->data = $data;
        return $self;
    }

    public function type(): ResultType
    {
        return $this->type;
    }

    public function layer(): LayerNumber
    {
        return $this->layer;
    }

    public function row(): Nrow
    {
        return $this->row;
    }

    public function column(): Ncol
    {
        return $this->column;
    }

    public function data(): TimeSeriesData
    {
        return $this->data;
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return array(
            'type' => $this->type->toString(),
            'layer' => $this->layer->toInt(),
            'row' => $this->row->toInt(),
            'column' => $this->column->toInt(),
            'data' => $this->data->toArray()
        );
    }
}
